<?php
/**
 * PHP_CodeSniffer Coding Standard.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   CVS: $Id: PHPCSCodingStandard.php 271053 2008-12-12 04:06:01Z squiz $
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

if (class_exists('PHP_CodeSniffer_Standards_CodingStandard', true) === false) {
    throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_Standards_CodingStandard not found');
}

/**
 * PHP_CodeSniffer Coding Standard.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   Release: 1.2.0
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class PHP_CodeSniffer_Standards_Bvb_BvbCodingStandard extends PHP_CodeSniffer_Standards_CodingStandard
{


    /**
     * Return a list of external sniffs to include with this standard.
     *
     * The PHP_CodeSniffer standard combines the PEAR and Squiz standards
     * but removes some sniffs from the Squiz standard that clash with
     * those in the PEAR standard.
     *
     * @return array
     */
    public function getIncludedSniffs()
    {
		// PEAR.NamingConventions.ValidVariableName -  protected and private should start with _
        return array(
                'PEAR',
                //'Squiz',
                'Zend',
                'Generic/Sniffs/WhiteSpace/ScopeIndentSniff.php'
               );

    }//end getIncludedSniffs()


    /**
     * Return a list of external sniffs to exclude from this standard.
     *
     * The PHP_CodeSniffer standard combines the PEAR and Squiz standards
     * but removes some sniffs from the Squiz standard that clash with
     * those in the PEAR standard.
     *
     * @return array
     */
    public function getExcludedSniffs() {
        return array(
                'PEAR/Sniffs/Files/LineEndingsSniff.php',
                'PEAR/Sniffs/Files/LineLengthSniff.php',
                'PEAR/Sniffs/Commenting/FileCommentSniff.php',
                'PEAR/Sniffs/Commenting/ClassCommentSniff.php',
                'PEAR/Sniffs/NamingConventions/ValidVariableNameSniff.php',
                'PEAR/Sniffs/NamingConventions/ValidFunctionNameSniff.php',
                'PEAR/Sniffs/WhiteSpace/ScopeIndentSniff.php',

                'Generic/Sniffs/PHP/DisallowShortOpenTagSniff.php',

                'Squiz/Sniffs/Classes/ClassFileNameSniff.php',
                'Squiz/Sniffs/Classes/ValidClassNameSniff.php',
                'Squiz/Sniffs/Commenting/ClassCommentSniff.php',
                'Squiz/Sniffs/Commenting/FileCommentSniff.php',
                'Squiz/Sniffs/Commenting/FunctionCommentSniff.php',
                'Squiz/Sniffs/Commenting/VariableCommentSniff.php',
                'Squiz/Sniffs/ControlStructures/SwitchDeclarationSniff.php',
                'Squiz/Sniffs/Files/FileExtensionSniff.php',
                'Squiz/Sniffs/NamingConventions/ConstantCaseSniff.php',
                'Squiz/Sniffs/WhiteSpace/ScopeIndentSniff.php',
        );

    }//end getExcludedSniffs()


}//end class
