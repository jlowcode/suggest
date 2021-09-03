<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

require_once JPATH_SITE . '/components/com_fabrik/models/element.php';

class PlgFabrik_ElementSuggest extends PlgFabrik_Element {

    public function render($data, $repeatCounter = 0)
    {
        $input = $this->app->input;

        $displayData = new stdClass;
        $displayData->access = in_array('2', $this->user->getAuthorisedViewLevels());
        $displayData->view = $input->get('view');
        $displayData->button = $this->getHTMLButton();
        $displayData->incompleteButton = $this->getIncompleteButton();

        $layout = $this->getLayout('details');

        return $layout->render($displayData);
    }

    public function elementJavascript($repeatCounter)
    {
        $id = $this->getHTMLId($repeatCounter);
        $formModel = $this->getFormModel();
        $listModel = $this->getListModel();
        $params = $this->getParams();
        $app = $this->app;

        $opts = $this->getElementJSOptions($repeatCounter);

        $opts->rowId = $formModel->getRowId();
        $opts->formId = $formModel->getId();
        $opts->listId = $listModel->getId();
        $opts->view = $app->input->get('view');
        $opts->situacaoName = $formModel->getElement($params->get('suggest_status'), true)->element->name;
        $opts->situacaoValue = $params->get('suggest_valor');
        $opts->idMasterName = $formModel->getElement($params->get('suggest_master'), true)->element->name;
        $opts->contribuidor = $formModel->getElement($params->get('suggest_contribuidor'), true)->element->name;
        $opts->listNameReview = $this->getListNameReview();
        $opts->userId = $this->user->id;
        $opts->creator = $formModel->getElement($params->get('suggest_user'), true)->element->name;
        $opts->creatorValue = $formModel->data[$formModel->getTableName() . '___' . $opts->creator . '_raw'];

        return array('FbSuggest', $id, $opts);
    }

    protected function getListNameReview() {
        $params = $this->getParams();
        $code = $params->get('suggest_list_name_review');

        $url = '';
        if (!empty($code)) {
            $url = eval($code);
        }

        return $url;
    }

    protected function getIncompleteButton() {
        $params = $this->getParams();

        $incompleteText = $params->get('suggest_button_porcentagem', '');

        if (empty($incompleteText)) {
            return $incompleteText;
        }

        $disconsider = json_decode($params->get('suggest_disconsider_elements', array()))->disconsider_element;

        $formModel = $this->getFormModel();
        $listName = $formModel->getTableName();

        $elementsName = array();
        $groups = $formModel->getGroupsHiarachy();
        foreach ($groups as $groupModel)
        {
            $elementModels = $groupModel->getPublishedElements();
            foreach ($elementModels as $elementModel)
            {
                $elementName = $elementModel->element->name;
                if (($elementName !== $this->element->name) && (!in_array($elementName, $disconsider))) {
                    $elementsName[] = $elementModel->element->name;
                }
            }
        }

        $qtdElements = count($elementsName);
        $qtdEmptyElements = 0;
        $data = $formModel->data;
        foreach ($elementsName as $item) {
            if (empty($data[$listName . '___' . $item])) {
                $qtdEmptyElements++;
            }
        }

        $percentage = round($qtdEmptyElements/$qtdElements*100);

        if (((int) $percentage === 0) || ((int) $percentage === 100)) {
            return '';
        }

        $infos = json_decode($params->get('suggest_button_porcentagem_style'));

        if (empty($infos)) {
            $class = "btn-default";
            $style = "#suggest_button_incomplete:hover {background-color: #cccccc !important;}";
        }
        else {
            $from = $infos->suggest_button_porcentagem_from;
            $to = $infos->suggest_button_porcentagem_to;
            $button_class = $infos->suggest_button_porcentagem_style_button;

            foreach ($button_class as $key => $item) {
                if (($percentage >= $from[$key]) && ($percentage <= $to[$key])) {
                    $class = $item;
                }
            }
        }

        switch ($class) {
            case 'btn-default':
                $style = "#suggest_button_incomplete:hover {background-color: #cccccc !important;}";
                break;
            case 'btn-primary':
                $style = "#suggest_button_incomplete:hover {background-color: #002699 !important;}";
                break;
            case 'btn-danger':
                $style = "#suggest_button_incomplete:hover {background-color: #ff0000 !important;}";
                break;
            case 'btn-success':
                $style = "#suggest_button_incomplete:hover {background-color: #206020 !important;}";
                break;
            case 'btn-warning':
                $style = "#suggest_button_incomplete:hover {background-color: #e67300 !important;}";
                break;
        }

        echo "<script>var head = document.getElementsByTagName('head')[0]; head.innerHTML += '<style>{$style}</style>';</script>";
        $button = "<button type='button' id='suggest_button_incomplete' class='btn {$class} button' disabled>{$percentage}% {$incompleteText}</button><br><br>";

        return $button;
    }

    protected function getHTMLButton() {
        $params = $this->getParams();

        $label = $params->get('suggest_button_label');
        $style = $params->get('suggest_button_style');

        $button = "<button type='button' id='suggest_button' class='btn {$style} button'>{$label}</button>";

        return $button;
    }

    private function deleteDuplicatedRow($id, $table) {
        $db = JFactory::getDbo();
        $query = "DELETE FROM {$table} WHERE id = '{$id}'";
        $db->setQuery($query);
        $db->execute();
    }

    public function onCloneRow() {
        $rowId = $_POST['rowId'];
        $listId = $_POST['listId'];
        $situacaoName = $_POST['situacaoName'];
        $situacaoValue = $_POST['situacaoValue'];
        $idMasterName = $_POST['idMasterName'];
        $listNameReview = $_POST['listNameReview'];
        $contribuidorName = $_POST['contribuidor'];
        $userId = $_POST['userId'];
        $creator = $_POST['creator'];
        $creatorValue = $_POST['creatorValue'];

        $listModel = JModelLegacy::getInstance('List', 'FabrikFEModel');
        $listModel->setId($listId);
        $idsToCopy = array();
        $idsToCopy[] = $rowId;
        $listModel->copyRows($idsToCopy);

        $update = array();
        $update['id'] = $listModel->lastInsertId;
        $update[$situacaoName] = $situacaoValue;
        $update[$idMasterName] = $rowId;
        $update[$contribuidorName] = $userId;
        $update[$creator] = $creatorValue;
        $update = (Object) $update;
        $listModel->updateObject($listModel->getGenericTableName(), $update, 'id');

        $link = $listNameReview . $update->id;

        $formModel = $listModel->getFormModel();
        $groupModels = $formModel->getGroupsHiarachy();
        foreach ($groupModels as $groupModel)
        {
            $elementModels = $groupModel->getPublishedElements();
            foreach ($elementModels as $elementModel)
            {
                $element = $elementModel->element;
                if ($element->plugin === 'fileupload') {
                    $elementParams = json_decode($element->params);
                    if ((bool) $elementParams->ajax_upload) {
                        $table = $listModel->getGenericTableName();
                        $db = JFactory::getDbo();
                        $query = $db->getQuery(true);
                        $query->select("id, {$element->name}")->from("{$table}_repeat_{$element->name}")->where("parent_id = '{$update->id}'");
                        $db->setQuery($query);
                        $result = $db->loadAssocList();

                        $files = array();
                        foreach ($result as $item) {
                            $files[] = $item[$element->name];
                        }

                        foreach ($result as $key => $item) {
                            if ((in_array($item[$element->name], $files)) || ($files[$key] !== $item[$element->name])) {
                                $this->deleteDuplicatedRow($item['id'], "{$table}_repeat_{$element->name}");
                            }
                        }
                    }
                }
            }
        }

        echo $link;
    }
}