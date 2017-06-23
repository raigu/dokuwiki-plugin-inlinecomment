# inlinecomment

[DokuWiki](https://www.dokuwiki.org) plugin that allows edit small inline comments directly on wiki page without going to the edit page.

# Compatibility

Tested with DokuWiki 2017-02-19b "Frusterick Manners".

# Installation

In dokuwiki go to *Adimin -> Extension Manager -> Manual Install*

Add into *Install from URL* this url: https://github.com/raigu/dokuwiki-plugin-inlinecomment/archive/master.zip

Click *Install*

# Usage

```
<inlinecomment>this is comment that will be displayed.</inlinecomment>
```

This will be shown in wikipage as a text with edit button:

<img src="https://raw.githubusercontent.com/raigu/dokuwiki-plugin-inlinecomment/master/docs/img/inlinecomment_normal.jpg" title="Inline comment example">

If you click on the edit button an edit text box is displayed:

<img src="https://raw.githubusercontent.com/raigu/dokuwiki-plugin-inlinecomment/master/docs/img/inlinecomment_edit.jpg" title="Inline comment in edit mode">


Comment changes are also saved into revisions history.

<img src="https://raw.githubusercontent.com/raigu/dokuwiki-plugin-inlinecomment/master/docs/img/inlinecomment_revision.jpg" title="Inline comment old revisions">


This component will warn if someone has changed the comment preventing accidental overrides.

<img src="https://raw.githubusercontent.com/raigu/dokuwiki-plugin-inlinecomment/master/docs/img/inlinecomment_error.jpg" title="Inline comment error">

## Authors

* Rait Kapp - developer

## License

This project is licensed under the MIT License.

## Thanks


For this plugin a code snippet of saving changes into wiki page is taken
from [dokuwiki-plugin-todo](https://github.com/leibler/dokuwiki-plugin-todo)

## Acknowledgments

This plugin is done as a contribution to the cause of [Volunteer Reserve Rescue Team](https://www.rpr.ee/english/).
They needed it in their wiki.