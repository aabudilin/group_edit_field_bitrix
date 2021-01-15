<?

\Bitrix\Main\Loader::includeModule('iblock');


class GroupEdit {

	/*array $arParams
		IBLOCK_ID
		SECTION_ID
	*/
	protected $arParams;
	protected $field;
	protected $limit = 5000;
	protected $log = array('count' => 0, 'items' => array());
	protected $is_logged = true;
	protected $mapProperties = array();

	public function __construct($arParams) {
		$this->arParams = $arParams;
	}

	public function getSections(): array
	{
		$result = array();
		$rsSection = \Bitrix\Iblock\SectionTable::getList(array(
		    	'filter' => array(
	        		'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
	        		'DEPTH_LEVEL' => array(1,2,3,4),
	    		),
				'order' => array('LEFT_MARGIN' => 'ASC'),
	    		'select' =>  array('ID','NAME', 'DEPTH_LEVEL'),
	    		'cache' => ['ttl' => 3600],
		));
		while ($arSection = $rsSection->fetch()) {
		    $result[] = $arSection;
		}

		return $result;
	}

	public function getFields()
	{
		
	}

	public function setLog($param): void
	{
		$this->is_logged = $param;
	}

	public function getProperties(): array
	{
		/*
			PROPERTY_TYPE - S - строка, N - число, F - файл, L - список, E - привязка к элементам, G - привязка к группам
			MULTIPLE - Множественность (Y|N)
		*/
		$properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$this->arParams['IBLOCK_ID']));
		while ($prop_fields = $properties->GetNext()) {
			$this->mapProperties[$prop_fields['CODE']] = $prop_fields;
		}

		$this->mapProperties['ELEMENT_META_TITLE'] = array(
			'NAME' => 'META_TITLE',
			'CODE' => 'ELEMENT_META_TITLE',
			'PROPERTY_TYPE' => 'seo_field',
		);
		$this->mapProperties['ELEMENT_META_DESCRIPTION'] = array(
			'NAME' => 'META_DESCRIPTION',
			'CODE' => 'ELEMENT_META_DESCRIPTION',
			'PROPERTY_TYPE' => 'seo_field',
		);
		$this->mapProperties['NAME'] = array(
			'NAME' => 'Название',
			'CODE' => 'NAME',
			'PROPERTY_TYPE' => 'field',
		);

		return $this->mapProperties;
	}

	public function setField(string $field): void
	{
		$this->field = $field;
	}

	protected function save (int $id, array $post): void
	{
		$el = new CIBlockElement;
		$arLoad = Array();

		switch ($this->mapProperties[$post['field']]['PROPERTY_TYPE']) {
			case 'seo_field':
				$arLoad["IPROPERTY_TEMPLATES"] = Array(
					$post['field'] => $post['value'],
				);
				$res = $el->Update($id, $arLoad);
				break;
			case 'field':
				$arLoad["NAME"] = $post['value'];
				$res = $el->Update($id, $arLoad);
				break;
			case 'S':
			case 'E':
			case 'G':
			case 'N':
				$prop = array (
					$post['field'] => $post['value']
				);
				CIBlockElement::SetPropertyValuesEx($id, $this->arParams['IBLOCK_ID'], $prop);
				break;
		}
	}

	public function process(array $post)
	{
		$filter = array ();
		$filter['=IBLOCK_ID'] = $this->arParams['IBLOCK_ID'];
		$filter['INCLUDE_SUBSECTIONS'] = 'Y';
		if (!empty($post['section'])) {
			$filter['=SECTION_ID'] = $post['section'];
		}

		$ids = $this->getMap($filter);
		foreach ($ids as $id) {
			$this->save($id['ID'],$post);
			if ($this->is_logged) {
				$this->log['count']++;
				$this->log['items'][] = array (
					'id' => $id['ID'],
					'status' => 'обновлен'
				);
			}
		}

	}

	protected function getMap($filter) {
		/*$dbItems = \Bitrix\Iblock\ElementTable::getList(array(
			'order' => array('SORT' => 'ASC'), // сортировка
			'select' => array('ID'),
			'filter' => $filter, 
			'limit' => $this->limit,
			)
		);*/

		$res = CIBlockElement::GetList(Array(), $filter, false, Array("nPageSize"=>2000), array('ID'));
		while($data = $res->Fetch()) {
			$result[] = $data;
		}

		return $result;
	}

	public function getLog(): array
	{
		return $this->log;
	}

}

?>
