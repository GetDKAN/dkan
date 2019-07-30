<?php

namespace Drupal\dkan_common\Util;

/**
 * Utilities relating to current request.
 */
trait RequestTrait {

    /**
     * Request object from current stack.
     *
     * @codeCoverageIgnore
     * @return \Symfony\Component\HttpFoundation\Request
     */
    protected function getCurrentRequest() {
        return \Drupal::request();
    }

    /**
     * Current request uri.
     * @return string
     */
    protected function getCurrentRequestUri() {
        return $this->getCurrentRequest()
                ->getRequestUri();
    }
    /**
     * Current request body.
     *
     * @return string|resource
     */
    protected function getCurrentRequestContent() {
        return $this->getCurrentRequest()
                ->getContent();
    }
}
