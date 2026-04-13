<?php

class ProxonConfigurator extends IPSModuleStrict
{
	public function GetConfigurationForm(): string
	{
		$form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);

		// Noting to do if parent is not active
		if ($this->HasActiveParent() === false) {
			return json_encode($form);
		}

		$ControlPanels = $this->SendDataToParent(json_encode([
			"DataID" => "{E310B701-4AE7-458E-B618-EC13A1A6F6A8}", 
			"Function" => 3, 
			"Address" => 210, 
			"Quantity" => 2, 
			"Data" => "",
		]));
		
		// No response results in empty list
		if ($ControlPanels === false) {			
			return json_encode($form);
		}
		
		$getInstanceID = function($ControlPanel) {
			$ids = IPS_GetInstanceListByModuleID("{9496FF42-B793-02E3-8271-541651A9085F}");
			foreach ($ids as $id) {
				if (IPS_GetInstance($id)['ConnectionID'] != IPS_GetInstance($this->InstanceID)['ConnectionID']) {
					continue;
				}
				if (IPS_GetProperty($id, "ControlPanel") == $ControlPanel) {
					return $id;
				}
			}
			return null;
		};

		// Convert 
		$ControlPanels = unpack("n*", substr($ControlPanels, 2));
		$ControlPanels = ($ControlPanels[2] << 16) + $ControlPanels[1];

		$form['actions'][0]['values'][] = [
			"name" => $this->Translate("ZBP"),
			"address" => 0,
			"create" => [
				"moduleID" => "{9496FF42-B793-02E3-8271-541651A9085F}",
				"configuration" => [
					"ControlPanel" => 0,
				],
			],
		];

		for ($i = 0; $i < 20; $i++) {
			if (($ControlPanels & (1 << $i)) == 0) {
				continue;
			}
			$name = sprintf($this->Translate("NBP %d"), $i + 1);
			if (($i+1) == 20) {
				$name = $this->Translate("HNBP");
			}
			$form['actions'][0]['values'][] = [
                "name" => $name,
                "address" => ($i + 1),
                "create" => [
                    "moduleID" => "{9496FF42-B793-02E3-8271-541651A9085F}",
                    "configuration" => [
                        "ControlPanel" => $i + 1,
					],
				],
				"instanceID" => $getInstanceID($i),
            ];
		}

		return json_encode($form);
	}
}
