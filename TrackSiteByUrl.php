<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackSiteByUrl;


use Piwik\Container\StaticContainer;
use Psr\Log\LoggerInterface;
use Piwik\Common;
use Piwik\Piwik;
use Piwik\UrlHelper;
use Piwik\Plugins\SitesManager\API as APISitesManager;


class TrackSiteByUrl extends \Piwik\Plugin
{

    /*
    private $logger;
    
    public function __construct($pluginName=false)
    {
        parent::__construct($pluginName);
        $this->logger = StaticContainer::getContainer()->get('Psr\Log\LoggerInterface');
    }
    */

    public function getUrlRecursive($url)
    {
        $recPath    =   array();
        $count      =   0;
        $parts      =   explode('/', $url);

        for ($i=count($parts); $i>=0; $i--) {
            if ($parts) {
                $recPath[$count++] = implode('/', $parts);
                array_pop($parts);
            }
        }       

        return $recPath;
    }
    
    public function getListHooksRegistered()
    {
        //$this->logger->debug("getListHooksRegistered");
        return array(
            "Tracker.Request.getIdSite" => 'getRequestIdSite'
        );
    }
    
    public function getRequestIdSite(&$idSite, $params)
    {   
        $url = Common::getRequestVar('url', '', 'string', $_REQUEST);

        $res = self::getSiteIdForUrl($url);
        if($res)
        {
            $idSite = $res;
        }
    }
    
    public function getSiteIdForUrl($url)
    {
        $id = null;
        $urlParse = @parse_url(Common::unsanitizeInputValue($url));
        if(isset($urlParse['host']))
        {
            $isSuperUser = Piwik::hasUserSuperUserAccess();
            Piwik::setUserHasSuperUserAccess();

            $protocol = '';
            if(isset($urlParse['scheme']))
            {
                $protocol = $urlParse['scheme'];
                if(UrlHelper::isLookLikeUrl($url))
                {
                    $protocol .= '://';
                }
            }
            $ret = $this->getUrlRecursive($urlParse["path"]);
            
            $hostUrl = $protocol . $urlParse['host'];
            for ($i=0; $i<sizeof($ret); $i++) {
                
                $hostUrl = $protocol. $urlParse['host'] . $ret[$i];
                
                $sites = APISitesManager::getInstance()->getSitesIdFromSiteUrl($hostUrl);
                if(count($sites))
                {
                    $id = $sites[0]['idsite'];
                    break;
                }
            }
            Piwik::setUserHasSuperUserAccess($isSuperUser);
        }
        return $id;
    }
}
