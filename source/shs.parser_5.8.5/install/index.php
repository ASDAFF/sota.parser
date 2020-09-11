<?

use \Bitrix\Main\ModuleManager;

IncludeModuleLangFile(__FILE__);

Class shs_parser extends CModule
{
    const MODULE_ID = 'shs.parser';
    var $MODULE_ID = 'shs.parser';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;
    var $strError = '';

    function __construct()
    {
        $arModuleVersion = array();
        include(dirname(__FILE__) . '/version.php');
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = GetMessage('shs.parser_MODULE_NAME');
        $this->MODULE_DESCRIPTION = GetMessage('shs.parser_MODULE_DESC');
        $this->PARTNER_NAME = GetMessage('shs.parser_PARTNER_NAME');
        $this->PARTNER_URI = GetMessage('shs.parser_PARTNER_URI');
    }

    function InstallDB($arParams = array())
    {
        global $DB, $DBType, $APPLICATION;
        $this->errors = false;
        $this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . self::MODULE_ID . "/install/db/" . $DBType . "/install.sql");
        if ($this->errors !== false) {
            $APPLICATION->ThrowException(implode('<br>', $this->errors));
            return false;
        } else {
            RegisterModuleDependences('shs.parser', 'startPars', 'shs.parser', 'ParserEventHandler', 'OnParserStart');
            RegisterModuleDependences('shs.parser', 'EndPars', 'shs.parser', 'ParserEventHandler', 'OnParserEnd');
        }
        return true;
    }

    function UnInstallDB($arParams = array())
    {
        global $DB, $DBType, $APPLICATION;
        $this->errors = false;
        if (!array_key_exists('save_tables', $arParams) || ($arParams['save_tables'] != 'Y')) {
            $this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . self::MODULE_ID . "/install/db/" . $DBType . "/uninstall.sql");
            $strSql = "SELECT ID FROM b_file WHERE MODULE_ID='" . self::MODULE_ID . "'";
            $rsFile = $DB->Query($strSql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);
            while ($arFile = $rsFile->Fetch()) CFile::Delete($arFile['ID']);
        }
        UnRegisterModuleDependences('shs.parser', 'startPars', 'shs.parser', 'ParserEventHandler', 'OnParserStart');
        UnRegisterModuleDependences('shs.parser', 'EndPars', 'shs.parser', 'ParserEventHandler', 'OnParserEnd');
        return true;
    }

    function InstallEvents()
    {
        return true;
    }

    function UnInstallEvents()
    {
        return true;
    }

    function InstallFiles($arParams = array())
    {
        if (is_dir($p = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/admin')) {
            if ($dir = opendir($p)) {
                while (false !== $item = readdir($dir)) {
                    if ($item == '..' || $item == '.' || $item == 'menu.php') continue;
                    file_put_contents($file = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/' . $item, '<' . '? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/' . self::MODULE_ID . '/admin/' . $item . '");?' . '>');
                }
                closedir($dir);
            }
        }
        if ($_ENV['COMPUTERNAME'] != 'BX') {
            CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/shs.parser/install/images/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/images/shs.parser', false, true);
            CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/shs.parser/install/themes/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes', false, true);
            CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/shs.parser/install/js/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/js/', false, true);
        }
        return true;
    }

    function UnInstallAgent()
    {
        CModule::IncludeModule('main');
        $dbAgent = CAgent::GetList(array(), array('MODULE_ID' => 'shs.parser'));
        while ($arAgent = $dbAgent->Fetch()) {
            CAgent::Delete($arAgent[ID]);
        }
    }

    function UnInstallFiles()
    {
        if (is_dir($p = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/admin')) {
            if ($dir = opendir($p)) {
                while (false !== $item = readdir($dir)) {
                    if ($item == '..' || $item == '.') continue;
                    unlink($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/' . self::MODULE_ID . '_' . $item);
                }
                closedir($dir);
            }
        }
        if (is_dir($p = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/components')) {
            if ($dir = opendir($p)) {
                while (false !== $item = readdir($dir)) {
                    if ($item == '..' || $item == '.' || !is_dir($p0 = $p . '/' . $item)) continue;
                    $dir0 = opendir($p0);
                    while (false !== $item0 = readdir($dir0)) {
                        if ($item0 == '..' || $item0 == '.') continue;
                        DeleteDirFilesEx('/bitrix/components/' . $item . '/' . $item0);
                    }
                    closedir($dir0);
                }
                closedir($dir);
            }
        }
        if ($_ENV['COMPUTERNAME'] != 'BX') {
            DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/shs.parser/install/themes/.default/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes/.default');
            DeleteDirFilesEx('/bitrix/themes/.default/icons/shs.parser/');
            DeleteDirFilesEx('/bitrix/images/shs.parser/');
        }
        return true;
    }

    function AddEventPostType()
    {
        $event = new CEventType;
        $message = new CEventMessage;
        $event->Add(array('EVENT_NAME' => 'SOTBIT_PARSER_START', 'NAME' => GetMessage('SOTBIT_PARSER_START'), 'LID' => 'ru', 'DESCRIPTION' => GetMessage('SOTBIT_PARSER_START_DESCRIPTION'),));
        $field['ACTIVE'] = 'Y';
        $field['EVENT_NAME'] = 'SOTBIT_PARSER_START';
        $field['LID'] = array('ru', 'en');
        $field['EMAIL_FROM'] = '#DEFAULT_EMAIL_FROM#';
        $field['EMAIL_TO'] = '#EMAIL_TO#';
        $field['BCC'] = '';
        $field['SUBJECT'] = GetMessage('event_parser_start_title');
        $field['BODY_TYPE'] = 'html';
        $field['MESSAGE'] = GetMessage('event_parser_start_text');
        $message->Add($field);
        $event->Add(array('EVENT_NAME' => 'SOTBIT_PARSER_END', 'NAME' => GetMessage('SOTBIT_PARSER_END'), 'LID' => 'ru', 'DESCRIPTION' => GetMessage('SOTBIT_PARSER_END_DESCRIPTION'),));
        $field['ACTIVE'] = 'Y';
        $field['EVENT_NAME'] = 'SOTBIT_PARSER_END';
        $field['LID'] = array('ru', 'en');
        $field['EMAIL_FROM'] = '#DEFAULT_EMAIL_FROM#';
        $field['EMAIL_TO'] = '#EMAIL_TO#';
        $field['BCC'] = '';
        $field['SUBJECT'] = GetMessage('event_parser_end_title');
        $field['BODY_TYPE'] = 'html';
        $field['MESSAGE'] = GetMessage('event_parser_end_text');
        $message->Add($field);
    }

    function DeleteEventPostType()
    {
        $event = new CEventType;
        $event->Delete('SOTBIT_PARSER_START');
        $event->Delete('SOTBIT_PARSER_END');
    }

    function DoInstall()
    {
        global $APPLICATION;
        $this->InstallFiles();
        $this->InstallDB();
        $this->AddEventPostType();
        RegisterModule(self::MODULE_ID);
    }

    function DoUninstall()
    {
        global $APPLICATION;
        UnRegisterModule(self::MODULE_ID);
        $this->DeleteEventPostType();
        $this->UnInstallDB();
        $this->UnInstallAgent();
        $this->UnInstallFiles();
    }
} ?>