This folder contains helper tools to validate our code agains coding start rules and to generate API documentation.
Because I use NetBeans 6.9.1 I added also support for this tools from NetBeans IDE.
Follow install instruction for each tool to get it up and running localy.

=== CodeSniffer ===
URL: http://pear.php.net/package/PHP_CodeSniffer/

Coding standards are slightly modified rules based on default PEAR and Zend standards delivered with CodeSniffer.
This rules are defined in classes under library\CodingStyle\CodeSniffer\Bvb\ folder.
They may be modified by team after agreement.

Install:
1. you need PEAR, there is many mauals on Iternet how to do it
2. pear install PHP_CodeSniffer

Windows batch script \library\CodingStyle\CodeSniffer\run.cmd takes full file name as parameter. The command on line 2 is working in Linux also.

=== phpDocumentor ===
URL: http://www.phpdoc.org/

Install:
1. you need PEAR, there is many mauals on Iternet how to do it
2. pear install PHP_CodeSniffer

If you don't use my NetBeans plugin you may look at /library/CodingStyle/IDE/NetBeans/codecheck.cmd how phpDocumentor could be executed to generate output.
API documentation is generated into /public/api folder, I added it into svn:ignore.

=== NetBeans Support ===
NetBenas formating funcion is quite good in 6.9.1 version. I exported my settings form PHP code formating into library\CodingStyle\IDE\NetBeans\formating.zip. Use it on own risk, I did not tested import.

If you follow next installation procedure you will get following functionality:
* there will be new "Run configuration" named "CodeStyle" in your Bvb project
* if you select this "CodeStyle" run configuration in toolbar you will be able to execute code sniffer on actual file or generate documentation for whole project
* pressing F6 will generate documentation
* pressing Shift+F6 will output result of CodeSniff actual file in NetBeans IDE Output windows
* all this is provided by /library/CodingStyle/IDE/NetBeans/codecheck.cmd script, which is for Windows, but should be easy changable for Bash or rewritten to PHP

Install:
1. you need to follow installation procedures for CodeSniffer and PEAR
2. PEAR library has to be in include path
3. right click on zfdatagid project root in "Projects" tab
4. select "Properties/Run Configuration"
5. click "New..." and enter "CodeStyle"
6. Fill the dialog:
    Run As: Script (run in command line)
    Use Default PHP Interpretter: <unchecked>
    PHP Interpreter: <root path of zfdatagrid project>/library/CodingStyle/IDE/NetBeans/codecheck.cmd
    Index File: public/
    Arguments: <root path of zfdatagrid project>
7. right click on zfdatagid project root in "Projects" tab an select "Set as Main Project"
8. choose "CodeStyle" in toolbar combobox
9. you are ready: open any PHP file from project and press Shift+F6


Martin Minka, 9.9.2010
at http://www.xtmotion.co.uk