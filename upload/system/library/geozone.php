<?php
if ($this->config->get($method . '_' . $code . '_debug')) {
				$this->log->write(':: ALIPAY DEBUG :: Geo Zone ID not configured!');	
			}

class Geozone {
	protected $registry;

	public function validateGeoZone($registry, $address, $method, $code, $total) {
		$this->registry = $registry;

		$this->load->model('localisation/country');

		if ($this->config->get($method . '_' . $code . '_total') && $this->config->get($method . '_' . $code . '_total') > $total) {
			if ($this->config->get($method . '_' . $code . '_debug')) {
				$this->log->write(':: ' . strtoupper($code) . ' DEBUG :: TOTAL :: Total amount configured is bigger than order total amount!');	
			}
			
			$status = false;
		} elseif ($this->config->get($method . '_' . $code . '_geo_address') == 'geo_zones' && !empty($address['country_id'])) {
			if (!$this->config->get($method . '_' . $code . '_geo_zone_id')) {
				if ($this->config->get($method . '_' . $code . '_debug')) {
					$this->log->write(':: ' . strtoupper($code) . ' DEBUG :: GEO ZONE :: Geo Zone ID not configured!');	
				}
				
				$status = true;
			} else {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get($method . '_' . $code . '_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . $address['zone_id'] . "' OR zone_id = '0')");

				if ($query->row) {
					if (!empty($address['zone_id'])) {
						$country_info = $this->model_localisation_country->getCountry($address['country_id']);

						if ($country_info && $country_info['status']) {
							$this->load->model('localisation/zone');

							$zone_info = $this->model_localisation_zone->getZone($address['zone_id']);

							if ($zone_info && $zone_info['status'] && $zone_info['country_id'] == $address['country_id']) {
								if ($this->config->get($method . '_' . $code . '_debug')) {
									$this->log->write(':: ' . strtoupper($code) . ' DEBUG :: GEO ZONE :: Zone: ' . htmlspecialchars_decode($zone_info['name']) . ' is active!');
								}
								
								$status = true;
							} else {
								if ($this->config->get($method . '_' . $code . '_debug')) {
									$this->log->write(':: ' . strtoupper($code) . ' DEBUG :: GEO ZONE :: Relative zone ID: ' . (int)$address['zone_id'] . ' is not active for country name: ' . htmlspecialchars_decode($country_info['name']) . '!');
								}
								
								$status = false;
							}
						} else {
							if ($this->config->get($method . '_' . $code . '_debug')) {
								$this->log->write(':: ' . strtoupper($code) . ' DEBUG :: GEO ZONE :: Relative country ID: ' . (int)$address['country_id'] . ' is not active!');
							}
							
							$status = false;
						}
					} else {
						$country_info = $this->model_localisation_country->getCountry($address['country_id']);
						
						if ($country_info && $country_info['status']) {
							if ($this->config->get($method . '_' . $code . '_debug')) {
								$this->log->write(':: ' . strtoupper($code) . ' DEBUG :: GEO ZONE :: Relative country: ' . htmlspecialchars_decode($country_info['name']) . ' is active!');
							}
							
							$status = true;
						} else {
							if ($this->config->get($method . '_' . $code . '_debug')) {
								$this->log->write(':: ' . strtoupper($code) . ' DEBUG :: GEO ZONE :: Relative country ID: ' . (int)$address['country_id'] . ' is not active!');
							}
							
							$status = false;	
						}
				} else {
					$status = false;
				}
			}
		} elseif ($this->config->get($method . '_' . $code . '_geo_address') == 'addresses' && !empty($address['postcode']) && !empty($address['country_id'])) {
			$country_info = $this->model_localisation_country->getCountry($address['country_id']);

			if ($country_info && $country_info['status'] && $country_info['postcode_required']) {
				$status = true;
			} else {
				$status = false;
			}
		} else {
			$status = false;
		}
		
		return $status;
	}
	
	public function __get($name) {
		return $this->registry->get($name);	
	}
}
