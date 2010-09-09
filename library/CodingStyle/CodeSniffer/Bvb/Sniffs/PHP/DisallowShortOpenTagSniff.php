<?php

if (class_exists('Generic_Sniffs_PHP_DisallowShortOpenTagSniff', true) === false) {
    throw new PHP_CodeSniffer_Exception('Class Generic_Sniffs_PHP_DisallowShortOpenTagSniff not found');
}

/**
 * Sniff to disallow short opentags but allow short open tages with echo
 *
 * @author Anton Voytenko
 */
class Bvb_Sniffs_PHP_DisallowShortOpenTagSniff
extends Generic_Sniffs_PHP_DisallowShortOpenTagSniff {

    /**
     * Returns an array of tokens this test wants to listen for.
     * Override parent Sniff method to allow short open tag with echo syntax (<?=)
     * and disallow short open tags (<?)
     *
     * @return array
     */
    public function register() {
        return array(
            T_OPEN_TAG,
        );

    }//end register()

}