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
		
		// Convert 
		$ControlPanels = unpack("N*", substr($ControlPanels, 2));

		for ($i = 0; $i < 20; $i++) {
			if (($ControlPanels[1] & (1 << $i)) == 0) {
				continue;
			}
			$name = sprintf($this->Translate("Controlpanel %d"), $i + 1);
			if ($i == 20) {
				$name = $this->Translate("Controlpanel Main");
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
            ];
		}

		return json_encode($form);
	}
}
