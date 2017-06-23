function InLineComment(index) {
    console.log('constructor: ', index);
    this.index = index;
};

InLineComment.prototype.edit = function () {
    this.createAndDisplayForm();
};

InLineComment.prototype.getEditButtonElement = function () {
    return jQuery('.inlinecomment-button[data-index=' + this.index + ']');
};

InLineComment.prototype.getCommentElement = function () {
    return jQuery('.inlinecomment[data-index=' + this.index + ']');
};

InLineComment.prototype.getFormElement = function () {
    return jQuery('form.inlinecomment-form[data-index=' + this.index + ']');
};

InLineComment.prototype.save = function () {
    var self = this;
    var $form = this.getFormElement();
    var newValue = $form.find('input[name=comment]').val();

    var $comment = this.getCommentElement();
    var oldValue = $comment.text();
    var pageId = $comment.data('pageid');

    this.removeFormError();
    this.disableForm();

    var whenCompleted = function (data) {
        if (data.result == 'OK') {
            // change comment text
            var $comment = self.getCommentElement();
            $comment.text(data.comment);

            self.removeForm();
        } else if (data.result == 'CHANGED') {
            console.log(data);
            self.setFormError('Someone has changed the comment to this: ' + data.message);
            setTimeout(function () {
                self.enableForm();
            }, 1000);
        } else if (data.result == 'ERR') {
            console.log(data);
            self.setFormError('Error: ' + data.message);
            setTimeout(function () {
                self.enableForm();
            }, 1000);
        } else {
            console.log(data);
            self.setFormError('Unknown error! Could not save. Refresh the page and try again.');
            setTimeout(function () {
                self.enableForm();
            }, 1000);
        }
    };

    jQuery.post(
        DOKU_BASE + 'lib/exe/ajax.php',
        {
            call: 'plugin_inlinecomment',
            index: this.index,
            old_comment: oldValue,
            new_comment: newValue,
            pageid: pageId
        },
        whenCompleted,
        'json'
    );

};

InLineComment.prototype.createAndDisplayForm = function () {
    var self = this;
    var $comment = this.getCommentElement();
    var $btn = this.getEditButtonElement();

    // hide comment element and edit button.
    $comment.css('display', 'none');
    $btn.css('display', 'none');

    // create and init form
    var form = '<form class="inlinecomment-form" data-index="' + this.index + '"> '
        + '<input type="text" value="' + $comment.text() + '" name="comment"/>'
        + '<input type="submit" value="save" />'
        + '<input type="hidden" value="' + this.index + '" name="index"/>'
        + '</form>';
    var $form = jQuery(form);
    $form.submit(function (e) {
        e.preventDefault();
        e.stopPropagation();

        self.save();
        return false;
    });

    // add form after button
    $btn.after($form);
};

InLineComment.prototype.disableForm = function () {
    var $form = this.getFormElement();
    $form.find('input').prop('disabled', true);
};


InLineComment.prototype.enableForm = function () {
    var $form = this.getFormElement();
    $form.find('input').prop('disabled', false);
};


InLineComment.prototype.removeForm = function () {
    var $comment = this.getCommentElement();
    var $btn = this.getEditButtonElement();
    var $form = this.getFormElement();

    $form.remove();

    $comment.css('display', 'inline');
    $btn.css('display', 'inline');
};

InLineComment.prototype.setFormError = function (message) {
    var $form = this.getFormElement();
    $error = jQuery('<span class="inlinecomment-error"></span>');
    $form.append($error);
    $error.text(message);
};

InLineComment.prototype.removeFormError = function (message) {
    var $form = this.getFormElement();
    var $error = $form.find('span.inlinecomment-error');
    if ($error.length) {
        jQuery($error).remove();
    }
};


jQuery(function () {
    jQuery('button.inlinecomment-button').click(function (e) {
        e.preventDefault();
        e.stopPropagation();

        var $this = jQuery(this);

        var instance = $this.data('instance');
        if (instance == undefined) {
            instance = new InLineComment($this.data('index'));
            $this.data('instance', instance);
        }

        console.log(instance);
        instance.edit();
    });
});
