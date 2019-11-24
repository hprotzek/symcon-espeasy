<?php

class EspeasySwitch extends IPSModule {

    public function Create() {
        parent::Create();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

        $this->RegisterPropertyString('sysname', '');
        $this->RegisterPropertyString('tskname', '');
        $this->RegisterPropertyString('valname', '');
        $this->RegisterPropertyString('gpio', '');
    }

    public function ApplyChanges() {
        parent::ApplyChanges();

        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

        //Setze Filter für ReceiveData
        $sysname = $this->ReadPropertyString('sysname');
        $tskname = $this->ReadPropertyString('tskname');
        $valname = $this->ReadPropertyString('valname');
        $this->SetReceiveDataFilter('.*' . $sysname . '/' . $tskname . '/' . $valname . '.*');

        $this->RegisterVariableBoolean('EspeasySwitch_State', $this->Translate('State'), '~Switch');
        $this->EnableAction('EspeasySwitch_State');
    }

    public function ReceiveData($JSONString) {
        $this->SendDebug('JSON', $JSONString, 0);
        if (!empty($this->ReadPropertyString('sysname')) &&
            !empty($this->ReadPropertyString('tskname')) &&
            !empty($this->ReadPropertyString('valname'))) {
            $data = json_decode($JSONString);
            $this->SendDebug('State Topic', $data->Topic, 0);
            $this->SendDebug('State Payload', $data->Payload, 0);
            SetValue($this->GetIDForIdent('EspeasySwitch_State'), $data->Payload);
        }
    }

    public function RequestAction($Ident, $Value) {
        $this->SendDebug(__FUNCTION__ . ' Value', $Value, 0);
        $this->SwitchMode($Value);
    }

    public function SwitchMode(bool $Value) {
        $gpio = $this->ReadPropertyString('gpio');
        $Topic = $this->ReadPropertyString('sysname') . '/cmd';
        $this->sendMQTT($Topic, 'GPIO,' . $gpio . ',' . $Value);
    }

    protected function sendMQTT($Topic, $Payload) {
        $Data['DataID'] = '{043EA491-0325-4ADD-8FC2-A30C8EEB4D3F}';
        $Data['PacketType'] = 3;
        $Data['QualityOfService'] = 0;
        $Data['Retain'] = false;
        $Data['Topic'] = $Topic;
        $Data['Payload'] = $Payload;

        $DataJSON = json_encode($Data, JSON_UNESCAPED_SLASHES);
        $this->SendDebug(__FUNCTION__ . 'Topic', $Data['Topic'], 0);
        $this->SendDebug(__FUNCTION__, $DataJSON, 0);
        $this->SendDataToParent($DataJSON);
    }
}