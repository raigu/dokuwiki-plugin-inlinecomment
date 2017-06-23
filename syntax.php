<?php
/**
 * DokuWiki Plugin inlinecomment (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Rait Kapp <raigur@gmail.com>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

class syntax_plugin_inlinecomment extends DokuWiki_Syntax_Plugin
{
    /**
     * @return string Syntax mode type
     */
    public function getType()
    {
        return 'substition';
    }

    /**
     * @return string Paragraph type
     */
    public function getPType()
    {
        return 'normal';
    }

    /**
     * @return int Sort order - Low numbers go before high numbers
     */
    public function getSort()
    {
        return 999;
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    public function connectTo($mode)
    {
        //$this->Lexer->addSpecialPattern('<inlinecomment>',$mode,'plugin_inlinecomment');
        $this->Lexer->addEntryPattern('<inlinecomment[\s]*?.*?>(?=.*?</inlinecomment>)', $mode, 'plugin_inlinecomment');
    }

    public function postConnect()
    {
        $this->Lexer->addExitPattern('</inlinecomment>', 'plugin_inlinecomment');
    }

    /**
     * Handler to prepare matched data for the rendering process.
     *
     * <p>
     * The <tt>$aState</tt> parameter gives the type of pattern
     * which triggered the call to this method:
     * </p>
     * <dl>
     * <dt>DOKU_LEXER_ENTER</dt>
     * <dd>a pattern set by <tt>addEntryPattern()</tt></dd>
     * <dt>DOKU_LEXER_MATCHED</dt>
     * <dd>a pattern set by <tt>addPattern()</tt></dd>
     * <dt>DOKU_LEXER_EXIT</dt>
     * <dd> a pattern set by <tt>addExitPattern()</tt></dd>
     * <dt>DOKU_LEXER_SPECIAL</dt>
     * <dd>a pattern set by <tt>addSpecialPattern()</tt></dd>
     * <dt>DOKU_LEXER_UNMATCHED</dt>
     * <dd>ordinary text encountered within the plugin's syntax mode
     * which doesn't match any pattern.</dd>
     * </dl>
     * @param $aMatch String The text matched by the patterns.
     * @param $aState Integer The lexer state for the match.
     * @param $aPos Integer The character position of the matched text.
     * @param $aHandler Object Reference to the Doku_Handler object.
     * @return Integer The current lexer state for the match.
     * @public
     * @see render()
     * @static
     */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        switch ($state) {
            case DOKU_LEXER_ENTER :
                if (!is_numeric($handler->inlinecomment_index)) {
                    $handler->inlinecomment_index = 0;
                }
                $handler->inlinecomment_content = null;
                break;
            case DOKU_LEXER_MATCHED :
                break;
            case DOKU_LEXER_UNMATCHED :
                $handler->inlinecomment_content = $match;
                break;
            case DOKU_LEXER_EXIT :
                $data = array(
                    'inlinecomment_content' => $handler->inlinecomment_content,
                    'inlinecomment_index' => $handler->inlinecomment_index
                );
                $handler->inlinecomment_index++;
                unset($handler->inlinecomment_content);
                return $data;
                break;
            case DOKU_LEXER_SPECIAL :
                break;
        }
        return array();
    }

    /**
     * Render xhtml output or metadata
     *
     * @param string $mode Renderer mode (supported modes: xhtml)
     * @param Doku_Renderer $renderer The renderer
     * @param array $data The data from the handler() function
     * @return bool If rendering was successful.
     */
    public function render($mode, Doku_Renderer $renderer, $data)
    {
        global $INFO;
        if ($mode == 'xhtml') {
            if (array_key_exists('inlinecomment_index', $data)) {
                $pageId = $INFO['id'];
                $renderer->doc .=
                    '<i class="inlinecomment" data-index=' . $data['inlinecomment_index'] . ' data-pageid="' . $pageId . '">'
                    . $data['inlinecomment_content']
                    . '</i>'
                    . '<button class="inlinecomment-button" data-index="' . $data['inlinecomment_index'] . '">edit</button>';
            }
            return true;
        } else {
            return false;
        }
    }
}

// vim:ts=4:sw=4:et:
