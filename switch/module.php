<?php

class EspeasySwitch extends IPSModule {

    public function Create() {
        parent::Create();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

        $this->RegisterPropertyString('sysname', '');
        $this->RegisterPropertyString('tskname', '');
        $this->RegisterPropertyString('valname', '');
        $this->RegisterPropertyString('gpio', '0');
        $this->RegisterPropertyBoolean('inverted', false);
    }

    public function ApplyChanges() {
        parent::ApplyChanges();

        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

        if (!empty($this->ReadPropertyString('sysname')) &&
            !empty($this->ReadPropertyString('tskname')) &&
            !empty($this->ReadPropertyString('valname'))) {

            $sysname = $this->ReadPropertyString('sysname');
            $tskname = $this->ReadPropertyString('tskname');
            $valname = $this->ReadPropertyString('valname');
            $this->SetReceiveDataFilter('.*' . $sysname . '/' . $tskname . '/' . $valname . '.*');
        }

        $this->RegisterVariableBoolean('EspeasySwitch_State', 'State', '~Switch');
        $this->EnableAction('EspeasySwitch_State');
    }

    public function ReceiveData($JSONString) {
        $this->SendDebug(__FUNCTION__, $JSONString, 0);
        $data = json_decode($JSONString);
        $this->SendDebug(__FUNCTION__ . ' Topic', $data->Topic, 0);
        $this->SendDebug(__FUNCTION__ . ' Payload', $data->Payload, 0);
        SetValue($this->GetIDForIdent('EspeasySwitch_State'), $this->getSwitchValue(boolval($data->Payload)));
    }

    public function RequestAction($Ident, $value) {
        $this->SendDebug(__FUNCTION__ . ' Value', $value, 0);
        $this->SwitchMode($value);
    }

    public function SwitchMode(bool $value) {
        $gpio = $this->ReadPropertyString('gpio');
        $topic = $this->ReadPropertyString('sysname') . '/cmd';
        $this->sendMQTT($topic, 'GPIO,' . $gpio . ',' . intval($value));
    }

    protected function sendMQTT($Topic, $Payload) {
        $Data['DataID'] = '{043EA491-0325-4ADD-8FC2-A30C8EEB4D3F}';
        $Data['PacketType'] = 3;
        $Data['QualityOfService'] = 0;
        $Data['Retain'] = false;
        $Data['Topic'] = $Topic;
        $Data['Payload'] = $Payload;

        $DataJSON = json_encode($Data, JSON_UNESCAPED_SLASHES);
        $this->SendDebug(__FUNCTION__ . ' Topic', $Data['Topic'], 0);
        $this->SendDebug(__FUNCTION__ . ' Payload', $Data['Payload'], 0);
        $this->SendDebug(__FUNCTION__, $DataJSON, 0);
        $this->SendDataToParent($DataJSON);
    }

    private function getSwitchValue(bool $value) {
        $inverted = $this->ReadPropertyBoolean('inverted');
        if ($inverted) {
            return !$value;
        }
        return $value;
    }
}