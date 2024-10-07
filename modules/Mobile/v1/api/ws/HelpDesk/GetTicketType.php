<?php
class Mobile_WS_GetTicketType extends Mobile_WS_Controller {
    function process(Mobile_API_Request $request) {
        $response = new Mobile_API_Response();
        $value = 'ticket_type';
        $picklistvaluesmap = getAllPickListValues($value);
        $pickList = [];
        foreach ($picklistvaluesmap as $targetValue) {
            array_push($pickList, array($value => decode_html($targetValue)));
        }
        $fieldListPicklist[$value] = $pickList;
        $response->setApiSucessMessage('Successfully Fetched Data');
        $response->setResult($fieldListPicklist);
        return $response;
    }
}
