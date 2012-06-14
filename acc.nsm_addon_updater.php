<?php

require PATH_THIRD.'nsm_addon_updater/config.php';

/**
 * NSM Addon Updater Accessory
 *
 * @package			NsmAddonUpdater
 * @version			1.2.1
 * @author			Leevi Graham <http://leevigraham.com> - Technical Director, Newism
 * @copyright 		Copyright (c) 2007-2012 Newism <http://newism.com.au>
 * @license 		Commercial - please see LICENSE file included with this distribution
 * @link			http://ee-garage.com/nsm-example-addon
 * @see				http://expressionengine.com/public_beta/docs/development/accessories.html
 */

class Nsm_addon_updater_acc 
{
	/**
	 * The accessory name
	 *
	 * @var string
	 **/
	var $name	 		= NSM_ADDON_UPDATER_NAME;

	/**
	 * Version
	 *
	 * @var string
	 **/
	var $version	 	= NSM_ADDON_UPDATER_VERSION;

	/**
	 * Description
	 *
	 * @var string
	 **/
	var $description	= 'Accessory for NSM Addon Updater.';

	/**
	 * Sections
	 *
	 * @var array
	 **/
	var $sections	 	= array();

	/**
	 * Cache lifetime
	 *
	 * @var int
	 **/
	var $cache_lifetime	= 86400;

	/**
	 * Is the addon in test mode
	 *
	 * @var boolean
	 **/
	var $test_mode		= true;

	/**
	 * Constructor
	 *
	 * @return void
	 * @author Leevi Graham
	 **/
	function __construct()
	{
		$this->addon_id = $this->id = NSM_ADDON_UPDATER_ADDON_ID;
	}

	/**
	* Set the sections and content for the accessory
	*
	* @access	public
	* @return	void
	*/
	function set_sections()
	{
		$EE =& get_instance();

		$EE->cp->load_package_js("accessory_tab");
		$EE->cp->load_package_css("accessory_tab");
		
		$this->_log(__LINE__.': RESETTING ADDON UPDATER LOG AT '.date('r').'...', true);
		
		$vars = array(
			'addons' => $this->getAddonsWithFeeds($EE->addons->_packages)
		);
		
		$this->sections['Available Updates'] = $EE->load->view("/accessory/index", $vars, TRUE);
	}

	public function getAddonsWithFeeds($addons)
	{
		$valid_addons = array();
		
		$this->_log(__LINE__.': Getting valid addons');
		
		foreach ($addons as $addon_id => $addon) {
			$config_file = PATH_THIRD . '/' . $addon_id . '/config.php';

			$this->_log(__LINE__.': Reading '.$config_file);
			
			if (!file_exists($config_file)) {
				$this->_log(__LINE__.': No file at '.$config_file);
				continue;
			}
			
			$this->_log(__LINE__.': Including config file '.$config_file);
			
			include $config_file;

			# Is there a file with the xml url?
			$this->_log('Checking for addon feed in '.$addon_id);
			
			if (isset($config['nsm_addon_updater']['versions_xml'])) {
				$valid_addons[$addon_id] = array(
					'addon_name' 		=> $config['name'],
					'installed_version' => $config['version'],
					'title' 			=> '',//(string)$version->title,
					'latest_version' 	=> '',//$version_number,
					'notes' 			=> '',//(string)$version->description,
					'docs_url' 			=> '',//(string)$version->link,
					'download' 			=> FALSE,
					'created_at'		=> '',//$version->pubDate,
					'extension_class' 	=> $addon_id
				);
			}
			
			unset($config);
			
		}
		return $valid_addons;
	}

	/**
	* Set the sections and content for the accessory
	*
	* @access	public
	* @return	void
	*/
	/* TODO: DELETE
	function process_ajax_feeds()
	{
		require_once 'error_handler.php';
		
		$EE =& get_instance();
		$EE->output->enable_profiler = 0;
		$versions = FALSE;
		
		$this->_log(__LINE__.': STARTING ADDON UPDATER FUNCTIONS AT '.date('r').'...', true);
		
		if ($feeds = $this->_updateFeeds()) {
			foreach ($feeds as $addon_id => $feed) {
				$namespaces = $feed->getNameSpaces(true);
				$latest_version = 0;

				$this->_log(__LINE__.': Including XML file: '.PATH_THIRD . '/' . $addon_id . '/config.php');
				
				include PATH_THIRD . '/' . $addon_id . '/config.php';
				$this->_log(__LINE__.': '.$addon_id.' at version '.$config['version']);
				
				if (!empty($feed->channel->item)) {
					foreach ($feed->channel->item as $version) {
						$ee_addon = $version->children($namespaces['ee_addon']);
						$version_number = (string)$ee_addon->version;

						$this->_log(__LINE__.': Checking version '.$version_number.' of '.$addon_id);

						if (version_compare($version_number, $config['version'], '>') && version_compare($version_number, $latest_version, '>') ) {
						    
							$this->_log(__LINE__.': '.$addon_id.' version '.$version_number.' more recent than installed '.$config['version']);
						
							$latest_version = $version_number;
							$versions[$addon_id] = array(
								'addon_name' 		=> $config['name'],
								'installed_version' => $config['version'],
								'title' 			=> (string)$version->title,
								'latest_version' 	=> $version_number,
								'notes' 			=> (string)$version->description,
								'docs_url' 			=> (string)$version->link,
								'download' 			=> FALSE,
								'created_at'		=> $version->pubDate,
								'extension_class' 	=> $addon_id
							);

							if ($version->enclosure) {
								$versions[$addon_id]['download'] = array(
									'url' => (string)$version->enclosure['url'],
									'type' =>  (string)$version->enclosure['type'],
									'size' => (string)$version->enclosure['length']
								);

								if (isset($config['nsm_addon_updater']['custom_download_url'])) {
									$versions[$addon_id]['download']['url'] = call_user_func($config['nsm_addon_updater']['custom_download_url'], $versions[$addon_id]);
								}
							}
						}
					}
				}
			}
		}
		
		$this->sections['Available Updates'] = $EE->load->view("/accessory/updates", array('versions' => $versions), TRUE); 
	}
	*/


	/**
	* Set the sections and content for the accessory
	*
	* @access	public
	* @return	void
	*/
	function process_ajax_version_request()
	{
		require_once 'error_handler.php';
		
		$EE =& get_instance();
		$EE->output->enable_profiler = 0;
		$addon_id = $EE->input->get('addon_id');
		
		$this->_log(__LINE__.': STARTING ADDON UPDATER FUNCTIONS FOR `'.$addon_id.'` AT '.date('r').'...');
		
		$versions = array();
		$feed = $this->_updateFeed($addon_id);
		if (!$feed) {
			return false;
		}
		
		$namespaces = $feed->getNameSpaces(true);
		$latest_version = 0;

		$this->_log(__LINE__.': Including config file: '.PATH_THIRD . '/' . $addon_id . '/config.php');
		
		include PATH_THIRD . '/' . $addon_id . '/config.php';
		
		$this->_log(__LINE__.': '.$addon_id.' at version '.$config['version']);
		if (!empty($feed->channel->item)) {
			foreach ($feed->channel->item as $version) {
				$ee_addon = $version->children($namespaces['ee_addon']);
				$version_number = (string) $ee_addon->version;

				$this->_log(__LINE__.': Checking version '.$version_number.' of '.$addon_id);

				if (version_compare($version_number, $config['version'], '>') && version_compare($version_number, $latest_version, '>') ) {
					
					$this->_log(__LINE__.': '.$addon_id.' version '.$version_number.' more recent than installed '.$config['version']);
					
				    $latest_version = $version_number;
					$versions = array(
						'addon_name' 		=> $config['name'],
						'installed_version' => $config['version'],
						'title' 			=> (string) $version->title,
						'latest_version' 	=> $version_number,
						'notes' 			=> (string) $version->description,
						'docs_url' 			=> (string) $version->link,
						'download' 			=> FALSE,
						'created_at'		=> $version->pubDate,
						'extension_class' 	=> $addon_id,
						'is_current'		=> false,
						'status'			=> 'Need new version'
					);

					if ($version->enclosure) {
						$versions['download'] = array(
							'url' => (string) $version->enclosure['url'],
							'type' =>  (string) $version->enclosure['type'],
							'size' => (string) $version->enclosure['length']
						);

						if (isset($config['nsm_addon_updater']['custom_download_url'])) {
							$versions['download']['url'] = call_user_func($config['nsm_addon_updater']['custom_download_url'], $versions);
						}
					}
				}
			}
		}
		
		$this->_log(__LINE__.': ADDON UPDATER FUNCTIONS COMPLETE FOR `'.$addon_id.'`.');
		
		if (empty($versions)) {
			$versions = array(
				'latest_version'	=> $config['version'],
				'is_current'		=> true,
				'status'			=> 'Up-to-date'
			);
		}
		
		echo json_encode($versions);
		
		exit;
	}

	// =======================
	// = XML Feeds Functions =
	// =======================

	/**
	 * Loads all the feeds from the cache or new from the server
	 *
	 * @version		1.0.0
	 * @since		Version 1.0.0
	 * @access		private
	 * @return		array An array of RSS feed XML
	 **/
	/* TODO: DELETE
	public function _updateFeeds()
	{
		$EE =& get_instance();

		require_once PATH_THIRD . NSM_ADDON_UPDATER_ADDON_ID . "/libraries/Epicurl.php";

		$sources = FALSE;
		$feeds = FALSE;
		$mc = EpiCurl::getInstance();

		$this->_log(__LINE__.': EpiCurl loaded, iterating over EE packages');

		foreach ($EE->addons->_packages as $addon_id => $addon) {
			$config_file = PATH_THIRD . '/' . $addon_id . '/config.php';

			$this->_log(__LINE__.': Reading '.$config_file);
			
			if (!file_exists($config_file)) {
				$this->_log(__LINE__.': No file at '.$config_file);
				continue;
			}
			
			$this->_log(__LINE__.': Including config file '.$config_file);
			
			include $config_file;

			# Is there a file with the xml url?
			$this->_log('Checking for addon feed in '.$addon_id);
			
			if (isset($config['nsm_addon_updater']['versions_xml'])) {
				$url = $config['nsm_addon_updater']['versions_xml'];

				$this->_log(__LINE__.': Addon feed for '.$addon_id.' is '.$url);
				# Get the XML again if it isn't in the cache
				if ($this->test_mode || ! $xml = $this->_readCache(md5($url))) {

					$this->_log(__LINE__.': Getting feed from '.$url);

					$c = FALSE;
					$c = curl_init($url);
					curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
					@curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
					$curls[$addon_id] = $mc->addCurl($c);
					$xml = FALSE;
					
					$this->_log(__LINE__.': '.$addon_id.' cURL: '.$curls[$addon_id]->code);
					
					if($curls[$addon_id]->code == "200" || $curls[$addon_id]->code == "302") {
						$this->_log(__LINE__.': Building successful cache at '.md5($url));
						$xml = $curls[$addon_id]->data;
						$this->_createCacheFile($xml, md5($url));
					} else {
						throw new ErrorException('Could not retrieve changelog for add-on `'.$addon_id.'`');
					}
				}
				
				$this->_log(__LINE__.': Reading XML for '.$addon_id);

				# If there isn't an error with the XML
				try {
					$xmlObject = @simplexml_load_string($xml, 'SimpleXMLElement',  LIBXML_NOCDATA);
					$this->_log(__LINE__.': XML successfully loaded for '.$addon_id);
					$feeds[$addon_id] = $xmlObject;
				} catch (ErrorException $e) {
					throw new ErrorException('Invalid XML file for add-on `'.$addon_id.'`');
				}
			}
			$this->_log(__LINE__.': Finished with '.$addon_id.' config file');
			unset($config);
		}
		$this->_log(__LINE__.': Returning update feeds');
		return $feeds;
	}
	*/
	
	/**
	 * Loads a single feed from the cache or new from the server
	 *
	 * @version		1.0.0
	 * @since		Version 1.0.0
	 * @access		private
	 * @return		array An array of RSS feed XML
	 **/
	public function _updateFeed($addon_id)
	{
		$EE =& get_instance();

		require_once PATH_THIRD . "nsm_addon_updater/libraries/Epicurl.php";

		$sources = FALSE;
		$version = FALSE;
		$mc = EpiCurl::getInstance();
		
		$this->_log(__LINE__.': EpiCurl loaded, iterating over EE packages');
		
		$addon = $EE->addons->_packages[$addon_id];
		
		$config_file = PATH_THIRD . '/' . $addon_id . '/config.php';

		$this->_log(__LINE__.': Reading '.$config_file);

		if (!file_exists($config_file)) {
			$this->_log(__LINE__.': No file at '.$config_file);
			continue;
		}
		
		$this->_log(__LINE__.': Including config file '.$config_file);

		include $config_file;

		# Is there a file with the xml url?
		if (isset($config['nsm_addon_updater']['versions_xml'])) {
			$url = $config['nsm_addon_updater']['versions_xml'];

			# Get the XML again if it isn't in the cache
			if ($this->test_mode || ! $xml = $this->_readCache(md5($url))) {

				$this->_log(__LINE__.': Getting feed from '.$url);

				$c = FALSE;
				$c = curl_init($url);
				curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
				@curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
				$curls[$addon_id] = $mc->addCurl($c);
				$xml = FALSE;
				
				$this->_log(__LINE__.': '.$addon_id.' cURL: '.$curls[$addon_id]->code);
				
				if($curls[$addon_id]->code == "200" || $curls[$addon_id]->code == "302") {
					$this->_log(__LINE__.': Building successful cache at '.md5($url));
					$xml = $curls[$addon_id]->data;
					$this->_createCacheFile($xml, md5($url));
				} else {
					throw new ErrorException('Could not retrieve changelog for add-on `'.$addon_id.'`');
				}
			}
		}

		# If there isn't an error with the XML
		try {
			$xmlObject = @simplexml_load_string($xml, 'SimpleXMLElement',  LIBXML_NOCDATA);
			$this->_log(__LINE__.': XML successfully loaded for '.$addon_id);
			$version = $xmlObject;
		} catch (ErrorException $e) {
			throw new ErrorException('Invalid XML file for add-on `'.$addon_id.'`');
		}

		unset($config);

		return $version;
	}


	/**
	 * Creates a cache file populated with data based on a URL
	 *
	 * @version		1.0.0
	 * @since		Version 1.0.0
	 * @access		private
	 * @param		$data string The data we need to cache
	 * @param		$url string A URL used as a unique identifier
	 * @return		void
	 **/
	private function _createCacheFile($data, $key)
	{
		$cache_path = APPPATH.'cache/' . NSM_ADDON_UPDATER_ADDON_ID;
		$filepath = $cache_path ."/". $key . ".xml";
	
		if (! is_dir($cache_path)) {
			mkdir($cache_path . "", 0777, TRUE);
		}
		if (! is_really_writable($cache_path)) {
			return;
		}
		if ( ! $fp = fopen($filepath, FOPEN_WRITE_CREATE_DESTRUCTIVE)) {
			
			$this->_log(__LINE__.': Unable to write cache file: '.$filepath);
			
			return;
		}

		flock($fp, LOCK_EX);
		fwrite($fp, $data);
		flock($fp, LOCK_UN);
		fclose($fp);
		chmod($filepath, DIR_WRITE_MODE);

		$this->_log(__LINE__.': Cache file written: '.$filepath);
	}

	/**
	 * Modify the download URL
	 *
	 * @version		1.0.0
	 * @since		Version 1.0.0
	 * @access		private
	 * @param		$versions array 
	 * @return		array Modified versions URL
	 **/
	private function _readCache($key)
	{
		$cache = FALSE;
		$cache_path = APPPATH.'cache/' . NSM_ADDON_UPDATER_ADDON_ID;
		$filepath = $cache_path ."/". $key . ".xml";
		$this->_log(__LINE__.': Reading cache file '.$filepath);
		
		if ( ! file_exists($filepath)) {
			return FALSE;
		}
		if ( ! $fp = fopen($filepath, FOPEN_READ)) {
			@unlink($filepath);
			$this->_log(__LINE__.': Error reading cache file. File deleted');
			return FALSE;
		}
		if ( ! filesize($filepath)) {
			@unlink($filepath);
			$this->_log(__LINE__.': Error getting cache file size. File deleted');
			return FALSE;
		}
		
		// randomise cache timeout by 0-10mins to stagger cache regen
		$cache_timeout = $this->cache_lifetime + (rand(0,10) * 3600);
		
		if ( (filemtime($filepath) + $cache_timeout) < time() ) {
			@unlink($filepath);
			$this->_log(__LINE__.': Cache file has expired. File deleted');
			return FALSE;
		}

		flock($fp, LOCK_SH);
		$cache = fread($fp, filesize($filepath));
		flock($fp, LOCK_UN);
		fclose($fp);
		
		$this->_log(__LINE__.': Cache '.$filepath.' successfully loaded');

		return $cache;
	}

	/**
	 * Modify the download URL
	 *
	 * @author your name
	 * @param $param
	 * @return return type
	 */
	public static function nsm_addon_updater_download_url($versions)
	{
		return $versions['download']['url'];
	}

	private function _log($data, $reset = false)
	{
		if (! $this->test_mode) {
			return;
		}
		
		$cache_path = APPPATH.'cache/' . NSM_ADDON_UPDATER_ADDON_ID;
		$filepath = $cache_path ."/debug_log.txt";
	
		if (! is_dir($cache_path)) {
			mkdir($cache_path . "", 0777, TRUE);
		}
		if (! is_really_writable($cache_path)) {
			return;
		}
		
		// resets the file contents
		if ($reset) {
			if (! $fp = fopen($filepath, FOPEN_WRITE_CREATE_DESTRUCTIVE)) {
				return;
			}
			flock($fp, LOCK_EX);
			fwrite($fp, '');
			flock($fp, LOCK_UN);
			fclose($fp);
		}
		
		if (! $fp = fopen($filepath, FOPEN_WRITE_CREATE)) {
			return;
		}
		
		flock($fp, LOCK_EX);
		fwrite($fp, "\n\n".$data);
		flock($fp, LOCK_UN);
		fclose($fp);
		chmod($filepath, DIR_WRITE_MODE);
		
	}

	public function process_read_log()
	{
		$cache_path = APPPATH.'cache/' . NSM_ADDON_UPDATER_ADDON_ID;
		$filepath = $cache_path ."/debug_log.txt";
		if (!file_exists($filepath)) {
			exit;
		}
		$fp = fopen($filepath, 'rb');
		if (!$fp) {
			exit;
		}
		$log = fread($fp, filesize($filepath));
		die('<pre>'.$log.'</pre>');
	}

}