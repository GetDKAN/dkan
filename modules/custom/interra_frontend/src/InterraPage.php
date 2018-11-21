<?php

namespace Drupal\interra_frontend;

class InterraPage {


  public function __construct($chunkId, $path = '/') {
    $this->chunkId = $chunkId;
    $p = explode('/', $path);
    if (count($p) > 1) {
      $path = $p[1];
    }
    $this->path = $path;
  }

  public function header() {
    return '
    <head>
      <script>
          (function(){
            var redirect = "' . $this->path . '"
            if (redirect && redirect != location.href) {
              history.replaceState(null, null, redirect);
            }
          })();
      </script>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width,initial-scale=1">
      <link rel="manifest" href="/interra/manifest.json">
      <meta name="mobile-web-app-capable" content="yes">
      <meta name="apple-mobile-web-app-title" content="interra catalog">
      <link rel="apple-touch-icon" sizes="120x120" href="/icon-120x120.png">
      <link rel="apple-touch-icon" sizes="152x152" href="/icon-152x152.png">
      <link rel="apple-touch-icon" sizes="167x167" href="/icon-167x167.png">
      <link rel="apple-touch-icon" sizes="180x180" href="/icon-180x180.png">
      <link rel="icon" href="/favicon.ico"/>
      <title>Interra Catalog</title>
    </head>';
  }

  public function build() {
    return '<!DOCTYPE html>' . $this->header() . $this->body();
  }

  public function body() {
    return
      '<html lang="en">
        <body>
          <noscript>If you are seeing this message, that means <strong>JavaScript has been disabled on your browser</strong>, please <strong>enable JS</strong> to make this app work.
          </noscript>
        <div id="app">
        </div>
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700" rel="stylesheet">
        <script type="text/javascript" src="/interra/main.' . $this->chunkId . '.js"></script>
      </body>
    </html>';
  }

}
