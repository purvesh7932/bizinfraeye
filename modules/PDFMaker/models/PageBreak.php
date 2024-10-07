<?php
/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_PageBreak_Model
{
    /**
     * @var bool|simple_html_dom
     */
    protected $content = false;

    /**
     * @var string
     */
    protected $pageBreak = '<pagebreak>';

    /**
     * @param string $value
     * @return self
     */
    public static function getInstance($value)
    {
        $self = new self();
        $self->setContent($value);

        return $self;
    }

    public function updateContent()
    {
        $htmlDom = $this->getHtmlDom();

        foreach ($htmlDom->find('table tr') as $trTag) {
            $trMap = $this->getRowMap($trTag);

            $this->cloneRows($trTag, $trMap);
        }

        $this->setContent($htmlDom->save());
    }

    /**
     * @return simple_html_dom
     */
    public function getHtmlDom()
    {
        PDFMaker_PDFMaker_Model::getSimpleHtmlDomFile();
        $content = $this->getContent();

        return str_get_html($content);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $value
     */
    public function setContent($value)
    {
        $this->content = $value;
    }

    /**
     * @param simple_html_dom_node $trTag
     * @return array
     */
    public function getRowMap($trTag)
    {
        $tdNum = 0;
        $trMap = [];

        foreach ($trTag->find('td') as $tdTag) {
            $tdNum++;

            foreach (explode('#pagebreak#', $tdTag->innertext) as $trNum => $tdContent) {
                $trNum++;
                $trMap['tr_' . $trNum]['td_' . $tdNum] = $tdContent;
            }
        }

        return $trMap;
    }

    /**
     * @param simple_html_dom_node $trTag
     * @param array $trMap
     */
    public function cloneRows($trTag, $trMap)
    {
        $trClones = $this->getRowClones($trTag, $trMap);
        $trImplode = $this->getImplodeText($trTag);

        $this->setOuterText($trTag, implode($trImplode, $trClones));
    }

    /**
     * @param simple_html_dom_node $trTag
     * @param array $trMap
     * @return array
     */
    public function getRowClones($trTag, $trMap)
    {
        $trClones = array();

        foreach ($trMap as $key => $tdValues) {
            $trClone = $trTag;
            $tdCloneNum = 0;

            foreach ($trClone->find('td') as $tdClone) {
                $tdCloneNum++;

                if ($trMap[$key]['td_' . $tdCloneNum]) {
                    $tdClone->innertext = $trMap[$key]['td_' . $tdCloneNum];
                } else {
                    $tdClone->innertext = '';
                }
            }

            array_push($trClones, $trClone->outertext);
        }

        return $trClones;
    }

    /**
     * @param simple_html_dom_node $trTag
     * @return string
     */
    public function getImplodeText($trTag)
    {
        $first = clone $trTag->parent();
        $first->innertext = '#explode#';
        $firstTags = explode('#explode#', $first->outertext);
        $result = $firstTags[1];

        if ('table' !== $first->tag) {
            $second = clone $first->parent();
            $second->innertext = '#explode#';
            $secondTags = explode('#explode#', $second->outertext);

            $result .= $secondTags[1];
            $result .= $secondTags[0];
            $result .= $this->getPageBreak();
        } else {
            $result .= $this->getPageBreak();
        }

        $result .= $firstTags[1];

        return $result;
    }

    /**
     * @return string
     */
    public function getPageBreak()
    {
        return $this->pageBreak;
    }

    /**
     * @param string $value
     */
    public function setPageBreak($value)
    {
        $this->pageBreak = $value;
    }

    /**
     * @param simple_html_dom_node $trTag
     * @param string $value
     */
    public function setOuterText($trTag, $value)
    {
        $trTag->outertext = $value;
    }
}