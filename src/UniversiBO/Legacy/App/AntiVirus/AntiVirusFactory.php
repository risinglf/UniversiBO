<?php
namespace UniversiBO\Legacy\App\AntiVirus;

use UniversiBO\Legacy\Framework\FrontController;
/**
 *
 * @package universibo
 * @author Ilias Bartolini <brain79@virgilio.it>
 * @license GPL, http://www.opensource.org/licenses/gpl-license.php
 * @copyright CopyLeft UniversiBO 2001-2005
 */
class AntiVirusFactory
{
    public static function getAntiVirus(FrontController $fc)
    {
        if ( $fc->getAppSetting('antiVirusEnable') == 'true' )
        {
            if ($fc->getAppSetting('antiVirusType') == 'clamav' )
            {
                $cmd = $fc->getAppSetting('antiVirusClamavCmd');
                $opts = $fc->getAppSetting('antiVirusClamavOpts');

                return new Clamav($cmd, $opts);
            }
            return false;
        }
        return false;
    }
}