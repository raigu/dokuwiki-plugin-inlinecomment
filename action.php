<?php
/**
 * DokuWiki Plugin inlinecomment (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Rait Kapp <raigur@gmail.com>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

class action_plugin_inlinecomment extends DokuWiki_Action_Plugin
{

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller)
    {

        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handle_ajax_call_unknown');

    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event event object by reference
     * @param mixed $param [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */

    public function handle_ajax_call_unknown(Doku_Event &$event, $param)
    {
        global $ID, $conf, $lang;

        if ($event->data != 'plugin_inlinecomment') {
            return;
        }

        //no other ajax call handlers needed
        $event->stopPropagation();
        $event->preventDefault();

        // check if all needed input variables are presented
        $expected = ['old_comment', 'new_comment', 'index', 'pageid'];
        foreach ($expected as $key) {
            if (!isset($_REQUEST[$key])) {
                $return = array('result' => 'ERR', 'message' => 'POST parameter ' . $key . ' missing!');
                $this->printJson($return);
                return;
            }
        }

        $index = (int)$_REQUEST['index'];
        $ID = cleanID(urldecode($_REQUEST['pageid']));
        $INFO = pageinfo();

        #Determine Permissions
        if (auth_quickaclcheck($ID) < AUTH_EDIT) {
            echo "You do not have permission to edit this file.\nAccess was denied.";
            return;
        }

        // Check, if page is locked
        if (checklock($ID)) {
            $locktime = filemtime(wikiLockFN($ID));
            $expire = dformat($locktime + $conf['locktime']);
            $min = round(($conf['locktime'] - (time() - $locktime)) / 60);

            $msg = $this->getLang('lockedpage') . ' ' . $lang['lockedby'] . ': ' . editorinfo($INFO['locked'])
                . ' ' . $lang['lockexpire'] . ': ' . $expire . ' (' . $min . ' min)';
            $this->printJson(array('result' => 'ERR', 'message' => $msg));
            return;
        }

        #Retrieve Page Contents
        $wikitext = rawWiki($ID);

        #Determine position of tag
        if ($index >= 0) {
            $index++;
            // index is only set on the current page with the inlinecomment
            // the occurances are counted, untill the index-th input is reached which is updated
            $tagStartPos = $this->_strnpos($wikitext, '<inlinecomment', $index);
            $tagEndPos = strpos($wikitext, '>', $tagStartPos) + 1;

            if ($tagEndPos > $tagStartPos) {
                $textEndPos = strpos($wikitext, '</inlinecomment', $tagEndPos);

                $currentComment = substr($wikitext, $tagEndPos, $textEndPos - $tagEndPos);

                if (trim($currentComment) != trim($_REQUEST['old_comment']) && trim($currentComment) != trim($_REQUEST['new_comment'])) {
                    $data = array(
                        'result' => 'CHANGED',
                        'message' => $currentComment,
                    );
                    $this->printJson($data);
                    return;
                }

                $wikitext = substr_replace($wikitext, $_REQUEST['new_comment'], $tagEndPos, $textEndPos - $tagEndPos);

                // save Update (Minor)
                lock($ID);
                // @date 20140714 le add todo text to minorchange, use different message for checked or unchecked
                // saveWikiText($ID, $wikitext, $this->getLang($checked?'checkboxchange_on':'checkboxchange_off').': '.$todoText, $minoredit = true);
                saveWikiText($ID, $wikitext, $index . 'th comment: ' . $_REQUEST['old_comment'] . ' -> ' . $_REQUEST['new_comment'], $minoredit = true);
                unlock($ID);

                $return = array(
                    'result' => 'OK',
                    'comment' => $_REQUEST['new_comment']
                );
                $this->printJson($return);
                return;

            }
        } else {
            $return = array(
                'result' => 'ERR',
                'message' => 'Invalid value of the POST parameter index.'
            );
            $this->printJson($return);
            return;
        }
    }

    /**
     * Encode and print an arbitrary variable into JSON format
     *
     * @param mixed $return
     */
    private function printJson($return)
    {
        header('Content-Type: application/json');
        $json = new JSON();
        echo $json->encode($return);
    }


    /**
     * Find position of $occurance-th $needle in haystack
     */
    private function _strnpos($haystack, $needle, $occurance, $pos = 0)
    {
        for ($i = 1; $i <= $occurance; $i++) {
            $pos = strpos($haystack, $needle, $pos) + 1;
        }
        return $pos - 1;
    }
}

// vim:ts=4:sw=4:et:
