<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackSiteByUrl\tests\Integration;

use Piwik\Access;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Plugins\TrackSiteByUrl\TrackSiteByUrl;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group TrackSiteByUrl
 * @group TrackSiteByUrlTest
 * @group Plugins
 */
class TrackSiteByUrlTest extends IntegrationTestCase
{
    private $plugin;
    private $idSite1;
    private $idSite1Subsite1;
    private $idSite1Subsite1Subsite2;
    private $idSite2;
    
    public function setUp()
    {
        parent::setUp();

        $access = Access::getInstance();
        $access->setSuperUserAccess(true);

        $this->plugin = new TrackSiteByUrl();
        
        $apiSitesManager = APISitesManager::getInstance();
        $this->idSite1 = $apiSitesManager->addSite("Site 1", "http://site1/");
        $this->idSite1Subsite1 = $apiSitesManager->addSite("Site 1 - Subsite 1", "http://site1/subsite1");
        $this->idSite1Subsite1Subsite2 = $apiSitesManager->addSite("Site 1 - Subsite 1 - Subsite 2", "http://site1/subsite1/subsite2");
        $this->idSite2 = $apiSitesManager->addSite("Site 2", "http://site2/");
    }

    public function test_getSiteIdForUrl_directHit()
    {
        $idTracked = $this->plugin->getSiteIdForUrl("http://site1/");
        $this->assertEquals($idTracked, $this->idSite1);
    }

    public function test_getSiteIdForUrl_directHitSubsite()
    {
        $idTracked = $this->plugin->getSiteIdForUrl("http://site1/subsite1");
        $this->assertEquals($idTracked, $this->idSite1Subsite1);
    }
    
    public function test_getSiteIdForUrl_bubbleUpSite()
    {
        $idTracked = $this->plugin->getSiteIdForUrl("http://site2/subsite1/subsite2/subsite3/subsite4/");
        $this->assertEquals($idTracked, $this->idSite2);
    }
    
    public function test_getSiteIdForUrl_bubbleUpSubsite()
    {
        $idTracked = $this->plugin->getSiteIdForUrl("http://site1/subsite1/subsite2/subsite3/subsite4/");
        $this->assertEquals($idTracked, $this->idSite1Subsite1Subsite2);
    }
}
