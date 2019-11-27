<?php

class EspeasySensor extends IPSModule {

    public function Create() {
        parent::Create();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

        $this->RegisterPropertyString('sysname', '');
        $this->RegisterPropertyString('tskname', '');
        $this->RegisterPropertyString('valname', '');
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

        $this->RegisterVariableFloat('EspeasySensor_State', 'Value', '');
    }

    public function ReceiveData($JSONString) {
        $this->SendDebug(__FUNCTION__, $JSONString, 0);
        $data = json_decode($JSONString);
        $this->SendDebug(__FUNCTION__ . ' Topic', $data->Topic, 0);
        $this->SendDebug(__FUNCTION__ . ' Payload', $data->Payload, 0);
        SetValue($this->GetIDForIdent('EspeasySensor_State'), $data->Payload);
    }
}