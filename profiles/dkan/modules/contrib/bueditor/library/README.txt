
This directory contains custom library files(js or css) that extend bueditor functionality.
In editor settings you can add these files into editor library in this format:
%BUEDITOR/library/popup.js
%BUEDITOR/library/popup.css

Do not edit these files and do not put your custom libraries here because:
  - They may get lost when you update the module
  - They won't be included in editor import/export operations

Instead, store all custom or edited libraries in a new directory under your files path.
Then in editor settings you can include them in this format:
%FILES/custom-directory/custom.js
%FILES/custom-directory/custom.css