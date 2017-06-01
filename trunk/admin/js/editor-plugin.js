(function () {
  tinymce.create('tinymce.plugins.linebreak', {
    init: function (editor, url) {
      editor.addCommand('mceAddLinebreak', function () {
        editor.insertContent( '[br]');
      });

      editor.addButton('linebreak', {
        title: 'linebreak',
        icon: 'icon dashicons-editor-break mce-i-dashicon',
        cmd: 'mceAddLinebreak',
        text: ' <br>'
      });
    },

    createControl: function (n, cm) {
      return null;
    },

    getInfo: function () {
      return {
        longname: 'linebreak',
        author: 'Crazypsycho',
        authorurl: 'https://github.com/crazypsycho',
        infourl: 'https://github.com/crazypsycho/',
        version: '1.0'
      }
    }
  });

  tinymce.PluginManager.add('linebreak', tinymce.plugins.linebreak);
})();