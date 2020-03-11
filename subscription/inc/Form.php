<?php
namespace kOOL\Subscription;

require_once 'FormException.php';

class Form
{
	protected $title;
	protected $fields;
	protected $groups;
	protected $data;
	protected $validationErrors;
	protected $overflow;
	protected $groupsEnableClause = "((`deadline` = '0000-00-00' OR `deadline` > NOW()) AND (`stop` = '0000-00-00' OR `stop` > NOW()))";
	protected $actionLinks = [];
	protected $mode;
	protected $editGroup;
	protected $notifications = [];
	protected $validated = null;

	public function __construct($data) {
		$this->title = $data['title'];
		$this->initGroups($data['groups']);
		$this->overflow = $data['overflow'];
		$fields = json_decode(utf8_encode($data['fields']),true);
		array_walk_recursive($fields,'utf8_decode_array');
		$this->initFields($fields);
	}

	public function addActionLink($link,$label) {
		$this->actionLinks[] = [$link,$label];
	}

	public function setMode($mode) {
		$this->mode = $mode;
	}

	protected function initGroups($groupAndRoleIds) {
		$groups = [];
		$roles = [];
		$this->groups = [];
		$groupAndRoleIds = array_filter(explode(',',$groupAndRoleIds));
		foreach($groupAndRoleIds as $groupAndRoleId) {
			if(!$groupAndRoleId) continue;
			$groupId = substr($groupAndRoleId,1,6);
			$groups[] = $groupId;
			$this->groups[$groupAndRoleId] = ['group' => $groupId];
			if(strlen($groupAndRoleId) == 15 && substr($groupAndRoleId,7,2) == ':r') {
				$roleId = substr($groupAndRoleId,9,6);
				$roles[] = $roleId;
				$this->groups[$groupAndRoleId]['role'] = $roleId;
			}
		}
		if($groups) {
			$groups = $this->dbSelect('ko_groups','WHERE id IN ('.implode(',',$groups).') AND '.$this->groupsEnableClause);
		}
		if($roles) {
			$roles = $this->dbSelect('ko_grouproles','WHERE id IN ('.implode(',',$roles).')');
		}
		foreach($this->groups as $i => &$groupAndRole) {
			if(isset($groups[$groupAndRole['group']])) {
				$groupAndRole['group'] = $groups[$groupAndRole['group']];
				if(isset($groupAndRole['role'])) {
					$groupAndRole['role'] = $roles[$groupAndRole['role']];
				}
			} else {
				unset($this->groups[$i]);
			}
		}
	}

	protected function initFields($fields) {
		$groupFields = [];
		foreach($fields as $field => $definition) {
			if(preg_match('/^g([0-9]{6})(?::d([0-9]{6}|ADDALL))?/',$field,$matches)) {
				if(!isset($matches[2])) {
					$this->fields[$field] = $definition;
					$groupFields[$matches[1]][] = $field;
				} else if($matches[2] == 'ADDALL') {
					$group = $this->dbSelect('ko_groups','WHERE id='.$matches[1],'datafields','','',true);
					$datafieldIds = array_filter(explode(',',$group['datafields']));
					if(isset($definition['excludeDatafields'])) {
						$datafieldIds = array_diff($datafieldIds,$definition['excludeDatafields']);
					}
					if($datafieldIds) {
						$datafields = $this->dbSelect('ko_groups_datafields','WHERE id IN('.implode(',',$datafieldIds).') AND private=0');
						foreach($datafields as $datafield) {
							$this->fields['g'.$matches[1].':d'.$datafield['id']] = [
								'label' => $datafield['description'],
								'datafield' => $datafield,
								'excludeOptions' => $definition['excludeOptions'][$datafield['id']],
							];
							$groupFields[$matches[1]][] = 'g'.$matches[1].':d'.$datafield['id'];
						}
					}
				} else {
					$datafield = $this->dbSelect('ko_groups_datafields','WHERE id='.$matches[2].' AND private=0','*','','',true);
					if($datafield) {
						$definition['datafield'] = $datafield;
						$this->fields[$field] = $definition;
					}
				}
			} else if(substr($field,0,6) == '_check') {
				$this->fields[$field] = [
					'html' => $definition,
					'mandatory' => true,
				];
			} else {
				$this->fields[$field] = $definition;
			}
		}
		if($groupFields) {
			$groups = $this->dbSelect('ko_groups','WHERE id IN ('.implode(',',array_keys($groupFields)).") AND (`deadline` = '0000-00-00' OR `deadline` > NOW()) AND ".$this->groupsEnableClause);
			foreach($groupFields as $groupId => $fields) {
				foreach($fields as $field) {
					if(isset($groups[$groupId])) {
						$this->fields[$field]['group'] = $groups[$groupId];
					} else {
						unset($this->fields[$field]);
					}
				}
			}
		}
	}

	public function setEditData($data) {
		$this->data = $data;
		$this->overflow = false;
		$rev = $this->dbSelect('ko_leute_mod','WHERE _leute_id='.$data['id'],'COUNT(*)','','',true);
		if(reset($rev) > 0) {
			$this->addNotification($this->getLL('subscription_form_notification_mod_exisiting'));
		}
	}

	public function setEditGroup($groupId) {
		if(!isset($this->groups[$groupId])) {
			throw new FormException('key_invalid');
		}
		$this->editGroup = $groupId;
	}

	public function addNotification($message) {
		$this->notifications[] = $message;
	}

	public function render() {
		if($this->mode != 'editLink' && empty($this->groups) && empty($this->data)) {
			throw new FormException('no_group');
		}
		echo '<h1>'.($this->mode == 'editLink' ? $this->getLL('subscription_form_edit_link') : $this->title).'</h1>';
		foreach($this->notifications as $message) {
			echo '<div class="notification">'.$message.'</div>';
		}
		echo '<form method="post" class="lpcForm kOOLSubscriptionForm">';
		if(!empty($this->groups)) {
			$this->renderFormGroup('groupSelect',$this->groups);
		}
		if($this->mode == 'editLink') {
			$this->renderFormGroup('email',[
				'label' => $this->getLL('kota_ko_leute_email'),
			]);
		} else {
			foreach($this->fields as $field => $definition) {
				$this->renderFormGroup($field,$definition);
			}
		}
		echo '<div class="lpcFormGroup aligned"><div>';
		echo '<button class="submitButton">'.$this->getLL($this->mode == 'editLink' ? 'subscription_form_send_edit_link' : 'subscription_form_submit').'</button>';
		foreach($this->actionLinks as list($link,$label)) {
			echo '<a href="'.$link.'" class="button">'.$label.'</a>';
		}
		echo '</div></div>';
		echo '</form>';
	}

	protected function renderFormGroup($field,$definition) {
		if(substr($field,0,8) == '_caption') {
			echo '<h2>'.$definition.'</h2>';
		} else if(substr($field,0,5) == '_text') {
			echo '<div class="lpcFormGroup"><p class="text">'.nl2br($definition).'</p></div>';
		} else if(substr($field,0,3) == '_hr') {
			echo '<hr />';
		} else {
			$input = $this->renderField($field,$definition);
			if($input) {
				if($input->getAttribute('type') == 'hidden') {
					echo $input->render();
				} else {
					$formGroup = new Tag('div');
					$formGroup->addClass('lpcFormGroup');
					if($input->getProp('required')) {
						$formGroup->addClass('mandatory');
					}
					if($label = $this->getLabel($field,$definition).$this->getHelpTooltip($definition)) {
						$formGroup->addContent('<label for="'.$field.'">'.$label.'</label>');
					} else {
						$formGroup->addClass('aligned');
					}
					$formGroup->addContent($input->render());
					if(isset($this->validationErrors[$field])) {
						foreach($this->validationErrors[$field] as $error) {
							$formGroup->addContent('<div class="lpcFormError"><div>'.$this->getLL('subscription_form_error_field_'.$error).'</div></div>');
						}
						$formGroup->addClass('error');
					}

					echo $formGroup->render();
				}
			}
		}
	}

	protected function renderField($field,$definition) {
		$tag = new Tag('input');
		$tag->setAttribute('name',$this->getInputName($field));
		$tag->setAttribute('id',$field);
		$tag->setProp('required',!empty($definition['mandatory']));
		$tag->setProp('readonly',!empty($this->data) && !empty($definition['noEdit']));
		if($field == 'groupSelect') {
			if(count($definition) > 1) {
				$tag->setTagName('select');
				$tag->setProp('required');
				$value = $this->getInputValue($field);
				if($this->editGroup) {
					$value = $this->editGroup;
				}
				foreach($definition as $combinedId => $groupAndRole) {
					if($this->editGroup && $combinedId != $this->editGroup) continue;
					// check group count
					$option = new Tag('option');
					$option->setAttribute('value',$combinedId);
					$option->setText($groupAndRole['group']['name']);
					if(isset($groupAndRole['role'])) {
						$option->addContent(': '.$groupAndRole['role']['name']);
					}
					if($groupAndRole['group']['deadline'] != '0000-00-00' && $groupAndRole['group']['deadline'] < date('Y-m-d')) {
						$option->setAttribute('data-error',$this->getLL('subscription_form_warning_deadline'));
					} else if($groupAndRole['group']['maxcount'] > 0
						&& $groupAndRole['group']['count'] >= $groupAndRole['group']['maxcount']
						&& (!$groupAndRole['group']['count_role']
							|| (isset($groupAndRole['role']) && $groupAndRole['group']['count_role'] == $groupAndRole['role']['id'])
						)
					) {
						$warning = $this->getLL('subscription_form_warning_maxcount');
						$hint = ' ('.getLL('subscription_form_booked_up');
						if($this->overflow) {
							$warning .= ' ('.getLL('subscription_form_overflow_subscription').')';
							$hint .= ', '.getLL('subscription_form_overflow_subscription');
						}
						$hint .= ')';
						$option->setAttribute($this->overflow ? 'data-warning' : 'data-error',$warning);
						$option->addContent($hint);
					}

					$option->setProp('selected',$value == $combinedId);
					$tag->addContent($option->render());
				}
			} else {
				$tag->setAttribute('type','hidden');
				$tag->setAttribute('value',key($definition));
			}
			$tag->addClass('lpcGroupSelect');
		} else if(preg_match('/^g([0-9]{6})(?::([rd])([0-9]{6}))?$/',$field,$matches)) {
			if(isset($matches[2]) && $matches[2] == 'd') {
				switch($definition['datafield']['type']) {
					case 'textarea':
						$tag->setTagName('textarea');
						$tag->setText($this->getInputValue($field));
						$tag->setSelfClosing(false);
						break;
					case 'checkbox':
						$tag->setAttribute('type','checkbox');
						break;
					case 'multiselect':
					case 'select':
						$tag->setTagName('select');
						$inputValues = array_filter(explode(',',$this->getInputValue($field)));
						$options = unserialize($definition['datafield']['options']);
						if(isset($definition['excludeOptions'])) {
							$options = array_diff($options,$definition['excludeOptions']);
						}
						if($definition['datafield']['type'] != 'multiselect') {
							$tag->addContent('<option></option>');
						}
						foreach(array_diff($inputValues,$options) as $inputValue) {
							$option = new Tag('option');
							$option->setText($inputValue);
							$option->setAttribute('value',$inputValue);
							$option->setProp('selected');
							$tag->addContent($option->render());
						}
						foreach($options as $value) {
							$option = new Tag('option');
							$option->setText($value);
							$option->setAttribute('value',$value);
							$option->setProp('selected',in_array($value,$inputValues));
							$tag->addContent($option->render());
						}
						if($definition['datafield']['type'] == 'multiselect') {
							$tag->setProp('multiple');
							$tag->setAttribute('size',count($options));
							$tag->setAttribute('name',$tag->getAttribute('name').'[]');
						}
						break;
					case 'text':
					default:
						$tag->setAttribute('value',$this->getInputValue($field));
						break;
				}
				$tag->addClass('datafieldInput');
			} else {
				// check group count
				if(!$this->overflow
					&& $definition['group']['maxcount'] > 0
					&& $definition['group']['count'] >= $definition['group']['maxcount']
					&& (!$definition['group']['count_role']
						|| (count($matches) == 4 && $matches[3] == 'r' && $groupAndRole['group']['count_role'] == $matches[4])
					)
				) return null;

				$tag->setAttribute('type','checkbox');
				$tag->setProp('checked',$this->getInputValue($field));
				$tag->setAttribute('value','1');
				$tag->addClass('groupInput');
			}
			$tag->setAttribute('data-group',substr($field,0,7));
		} else if(substr($field,0,6) == '_check') {
			$tag->setAttribute('type','checkbox');
			$tag->append('<div>'.$definition['html'].'</div>');
		} else {
			$kota = $this->getKOTA('ko_leute')[$field]['form'];
			switch($kota['type']) {
				case 'text':
					$tag->setAttribute('value',$this->getInputValue($field));
					break;
				case 'select':
					$tag->setTagName('select');
					$inputValue = $this->getInputValue($field);
					foreach($kota['values'] as $i => $value) {
						$option = new Tag('option');
						$option->setAttribute('value',$value);
						$option->setText($kota['descs'][$i]);
						$option->setProp('selected',$value == $inputValue);
						$tag->addContent($option->render());
					}
					break;
				case 'textarea':
					$tag->setTagName('textarea');
					$tag->setText($this->getInputValue($field));
					$tag->setSelfClosing(false);
					break;
				case 'textplus':
					$renderedOptions = '';
					foreach($this->getTextplusOptions($field,$definition) as $value => $label) {
						$option = new Tag('option');
						$option->setAttribute('value',$value);
						$option->setText($label);
						$option->setProp('selected',$value == $inputValue);
						$renderedOptions .= $option->render();
					}
					if(empty($definition['renderAsInput'])) {
						$tag->setTagName('select');
						$tag->addContent($renderedOptions);
						$tag->setSelfClosing(false);
					} else {
						$tag->setAttribute('list',$field.'_list');
						$tag->append(
							'<datalist id="'.$field.'_list">'.
								'<select onchange="$(\'#'.$field.'\').val(this.value);">'.$renderedOptions.'</select>'.
							'</datalist>');
						$tag->setAttribute('value',$this->getInputValue($field));
					}
					break;
				case 'jsdate':
					$tag->addClass('datetimepicker');
					$tag->setAttribute('value',$this->getInputValue($field));
					break;
				default:
					return null;
			}
			if(!empty($kota['html_type'])) {
				$tag->setAttribute('type',$kota['html_type']);
			}
			if(isset($this->autocompleteFields[$field])) {
				$tag->setAttribute('autocomplete',$this->autocompleteFields[$field]);
			}
		}
		if($tag->getAttribute('type') == 'checkbox') {
			if($tag->getProp('readonly')) {
				$checkbox = clone $tag;
				$checkbox->setAttribute('type','hidden');
				$checkbox->setProp('readonly',false);
				$tag->setProp('disabled');
				$tag->removeAttribute('name');
				$tag->append($checkbox->render());
			}
			$tag->prepend('<div class="lpcCheckbox">');
			$tag->append('</div>');
		}
		return $tag;
	}

	protected function getTextplusOptions($field,$definition) {
		$explicitOptions = array_filter(array_map('trim',explode("\n",isset($definition['options']) ? $definition['options'] : '')));
		if($explicitOptions) {
			$options = [];
			foreach($explicitOptions as $o) {
				$s = array_map('trim',explode('|',$o,2));
				$options[$s[0]] = isset($s[1]) ? $s[1] : $s[0];
			}
			if(!isset($options[''])) {
				$options = ['' => ''] + $options;
			}
			return $options;
		} else {
			$kota = $this->getKOTA('ko_leute')[$field]['form'];
			if(isset($kota['values'])) {
				return array_combine($kota['values'],$kota['values']);
			} else {
				$values = db_select_distinct('ko_leute', $field, '', $kota['form']['where'], $kota['form']['select_case_sensitive'] ? TRUE : FALSE);
				return array_combine($values,$values);
			}
		}
	}

	protected function getLabel($field,$definition) {
		if($field == 'groupSelect') {
			$label = $this->getLL('subscription_form_group_select_label');
		} else {
			$label = $definition['label'];
		}
		return $label;
	}

	protected function getHelpTooltip($definition) {
		if(!empty($definition['help'])) {
			return '<b class="helpTooltip" title="'.$definition['help'].'">?</b>';
		}
		return '';
	}

	protected function getInputName($field) {
		return $field;
	}

	protected function getInputValue($field,$ignorePost = false) {
		if(isset($_POST[$field]) && !$ignorePost) {
			return $_POST[$field];
		}
		if(!empty($this->data)) {
			if(preg_match('/^g([0-9]{6})(?::([rd])([0-9]{6}))?$/',$field,$matches)) {
				$groupId = $matches[1];
				if(isset($matches[2]) && $matches[2] == 'd') {
					$datafieldData = $this->dbSelect('ko_groups_datafields_data','WHERE group_id='.$matches[1].' AND datafield_id='.$matches[3].' AND person_id='.$this->data['id'].' AND deleted=0','value','','',true);
					if($datafieldData) {
						return $datafieldData['value'];
					}
				}
				else {
					return strpos($this->data['groups'],$field) !== false;
				}
			} else {
				if(isset($this->data[$field])) {
					if($this->getKOTA('ko_leute')[$field]['form']['type'] == 'jsdate') {
						return strpbrk($this->data[$field],'123456789') == false ? '' : \DateTime::createFromFormat('Y-m-d',$this->data[$field])->format('d.m.Y');
					} else {
						return $this->data[$field];
					}
				}
			}
		}
		return '';
	}

	public function validate($data) {
		$this->validationErrors = [];
		$this->validated = [];
		$datafields = [];
		$datafieldData = [];
		$datafieldErrors = [];
		$additionalGroups = [];
		if(!empty($this->groups)) {
			if(empty($data['groupSelect'])) {
				$this->validationErrors['groupSelect'][] = 'missing';
			} else if(!isset($this->groups[$data['groupSelect']])) {
				$this->validationErrors['groupSelect'][] = 'invalid';
			} else {
				$this->validated['_group_id'] = $data['groupSelect'];
			}
		}
		foreach($this->fields as $field => $definition) {
			if($field[0] == '_' && substr($field,0,6) != '_check') continue;
			if(!empty($this->data) && !empty($definition['noEdit']) && $this->getInputValue($field,true) != $data[$field]) {
				$this->validationErrors[$field][] = 'noEdit';
			} else if(empty($data[$field]) && !empty($definition['mandatory'])) {
				if(preg_match('/^g([0-9]{6}):d[0-9]{6}$/',$field,$matches)) {
					$datafieldErrors[$matches[1]][$field][] = 'missing';
				} else {
					$this->validationErrors[$field][] = 'missing';
				}
			} else {
				$value = isset($data[$field]) ? $data[$field] : '';
				if(preg_match('/^g([0-9]{6})(?::([rd])([0-9]{6}))?$/',$field,$matches)) {
					if(isset($matches[2]) && $matches[2] == 'd') {
						if($this->validateDatafield($field,$value,$error)) {
							$datafieldData[$matches[1]][$matches[3]] = $value;
						} else {
							$datafieldErrors[$matches[1]][$field][] = $error;
						}
					} else {
						$this->validated['_additional_group_ids'][$field] = $value;
						if($value) {
							$additionalGroups[] = $matches[1];
						}
					}
				} else if(substr($field,0,6) != '_check') {
					if($this->validateKotaField($field,$definition,$value,$error)) {
						$this->validated[$field] = $value;
					}
					if($error) {
						$this->validationErrors[$field][] = $error;
					}
				}
			}
		}
		foreach($datafieldData as $groupId => $data) {
			if(isset($this->validated['_group_id']) && substr($this->validated['_group_id'],1,6) == $groupId) {
				$this->validated['_group_datafields'] = $data;
				if(isset($datafieldErrors[$groupId])) {
					$this->validationErrors = array_merge($this->validationErrors,$datafieldErrors[$groupId]);
				}
			} else if(in_array($groupId,$additionalGroups)) {
				$this->validated['_additional_group_datafields'][$groupId] = $data;
				if(isset($datafieldErrors[$groupId])) {
					$this->validationErrors = array_merge($this->validationErrors,$datafieldErrors[$groupId]);
				}
			}
		}
		if(empty($this->validationErrors)) {
			return $this->validated;
		}
		return false;
	}

	protected function validateKotaField($field,$definition,&$value,&$error) {
		$kota = $this->getKOTA('ko_leute')[$field]['form'];
		$error = false;
		switch($kota['type']) {
			case 'select':
				if(!in_array($value,$kota['values'])) {
					$error = 'invalid';
				}
				break;
			case 'jsdate':
				if(!$value) {
					$value = '0000-00-00';
				} else {
					$date = \DateTime::createFromFormat('d.m.Y',$value);
					if($date) {
						$value = $date->format('Y-m-d');
					} else {
						$error = 'invalid';
					}
				}
				break;
			case 'textplus':
				if(empty($definition['renderAsInput'])) {
					if(!array_key_exists($value,$this->getTextplusOptions($field,$definition))) {
						$error = 'invalid';
					}
					break;
				}
				// else fallthrough
			case 'text':
			case 'textarea':
				if($kota['html_type'] == 'email') {
					if($value && !check_email($value)) {
						$error = 'invalid';
					}
				} else {
					$value = format_userinput($value,'text');
				}
				break;
			default:
				return false;
		}
		return !$error;
	}

	protected function validateDatafield($field,&$value,&$error) {
		$error = false;
		switch($this->fields[$field]['datafield']['type']) {
			case 'select':
				$value = $value ? [$value] : [];
				//fallthrough
			case 'multiselect':
				$options = unserialize($this->fields[$field]['datafield']['options']);
				$valid = true;
				foreach($value as $v) {
					// check if selected options are allowed, allow excluded if it was already set before
					if(!in_array($v,$options) || (in_array($v,$this->fields[$field]['excludeOptions']) && $this->getInputValue($field,true) != $v)) {
						$valid = false;
					}
				}
				if($valid) {
					$value = implode(',',$value);
				} else {
					$error = 'invalid';
				}
				break;
			case 'checkbox':
				$value = !empty($value);
				break;
			default:
				$value = format_userinput($value,'text');
		}
		return !$error;
	}

	protected function getLL($key) {
		return getLL($key);
	}

	protected function dbSelect($table, $where = '', $columns = '*', $order = '', $limit = '', $single = false, $no_index = false) {
		return db_select_data($table,$where,$columns,$order,$limit,$single,$no_index);
	}

	protected function getKOTA($table) {
		global $KOTA;
		static $kota = [];

		if(!isset($kota[$table])) {
			ko_include_kota(array($table));
			$kota[$table] = $KOTA[$table];
		}
		return $kota[$table];
	}

	public function getPresentationData() {
		if($this->validated === null) {
			throw new \BadMethodCallException(__METHOD__.'() must not be called without a prior successful call to '.__CLASS__.'::validate()');
		}
		$data = [];
		if($this->groups) {
			$groupAndRole = $this->groups[$this->validated['_group_id']];
			$data['groupSelect']['value'] = $groupAndRole['group']['name'];
			if(isset($groupAndRole['role'])) {
				$data['groupSelect']['value'] .= ': '.$groupAndRole['role']['name'];
			}
			$data['groupSelect']['label'] = $this->getLabel('groupSelect',null);
		}
		foreach($this->fields as $field => $definition) {
			if(substr($field,0,6) == '_check') {
				$data[$field] = [
					'value' => $this->getLL('yes'),
					'label' => implode(
						'<br/>',
						array_filter(
							array_map(
								function($s) {
									return trim(strip_tags($s,'<a><i><b><em><strong><br>'));
								},
								explode('</p>',$definition['html'])
							)
						)
					),
				];
			} else if($field[0] != '_') {
				$type = '';

				if(preg_match('/^g([0-9]{6})(?::([rd])([0-9]{6}))?$/',$field,$matches)) {
					if(isset($matches[2]) && $matches[2] == 'd') {
						$type = $definition['datafield']['type'];
						if($type == 'select' || $type == 'multiselect') {
							$type = 'text';
						}
						if('g'.$matches[1] == $this->validated['_group_id']) {
							$value = $this->validated['_group_datafields'][$matches[3]];
						} else {
							$value = $this->validated['_additional_group_datafields'][$matches[1]][$matches[3]];
						}
					} else {
						$type = 'switch';
						$value = $this->validated['_additional_group_ids'][$field];
					}
				} else {
					$type = $this->getKOTA('ko_leute')[$field]['form']['type'];
					$value = $this->validated[$field];
				}
				switch($type) {
					case 'switch':
					case 'checkbox':
						$data[$field]['value'] = $this->getLL($value ? 'yes' : 'no');
						break;
					case 'select':
						$kota = $this->getKOTA('ko_leute')[$field]['form'];
						$data[$field]['value'] = $kota['descs'][array_search($value,$kota['values'])];
						break;
					case 'jsdate':
						$data[$field]['value'] = $value == '0000-00-00' ? '' : \DateTime::createFromFormat('Y-m-d',$value)->format('d.m.Y');
						break;
					case 'textarea':
						$data[$field]['value'] = nl2br($value);
						break;
					default:
						$data[$field]['value'] = $value;
				}
				$data[$field]['label'] = $this->getLabel($field,$definition);
			}
		}
		return $data;
	}

	protected $autocompleteFields = [
		'vorname' => 'given-name',
		'nachname' => 'family-name',
		'anrede' => 'honorific-prefix',
		'firm' => 'organization',
		'adresse' => 'address-line1',
		'adresse_zusatz' => 'address-line2',
		'plz' => 'postal-code',
		'ort' => 'address-level2',
		'land' => 'country-name',
		'telp' => 'home tel-national',
		'telg' => 'work tel-national',
		'natel' => 'mobile tel-national',
		'fax' => 'fax tel-national',
		'email' => 'email',
		'web' => 'url',
		'geburtsdatum' => 'bday',
		'geschlecht' => 'sex',
	];
}

class Tag
{
	protected $tagName;
	protected $attributes = [];
	protected $classes = [];
	protected $content = '';
	protected $selfClosing = true;
	protected $props = [];
	protected $prepend = '';
	protected $append = '';

	public function __construct($tagName) {
		$this->tagName = $tagName;
	}

	public function setTagName($tagName) {
		$this->tagName = $tagName;
	}

	public function setAttribute($name,$value) {
		if($name == 'class') {
			foreach(explode(' ',$value) as $class) {
				$this->addClass($class);
			}
		} else {
			$this->attributes[$name] = $value;
		}
	}

	public function getAttribute($name) {
		if($name == 'class') {
			return implode(' ',$this->classes);
		} else if(isset($this->attributes[$name])) {
			return $this->attributes[$name];
		} else {
			return null;
		}
	}

	public function removeAttribute($name) {
		if($name == 'class') {
			$this->classes = [];
		} else {
			unset($this->attributes[$name]);
		}
	}

	public function addClass($class) {
		if(!in_array($class,$this->classes)) {
			$this->classes[] = $class;
		}
	}

	public function setContent($content) {
		$this->content = $content;
	}

	public function setText($text) {
		$this->content = htmlspecialchars($text,ENT_COMPAT|ENT_HTML5,'ISO-8859-1');
	}

	public function addContent($content) {
		$this->content .= $content;
	}

	public function setSelfClosing($selfClosing) {
		$this->selfClosing = $selfClosing;
	}

	public function setProp($prop,$active = true) {
		if($active && !isset($this->attributes[$prop])) {
			$this->attributes[$prop] = true;
		}
		if(!$active && isset($this->attributes[$prop])) {
			unset($this->attributes[$prop]);
		}
	}

	public function getProp($prop) {
		return !empty($this->attributes[$prop]);
	}

	public function prepend($content) {
		$this->prepend = $content.$this->prepend;
	}

	public function append($content) {
		$this->append .= $content;
	}

	public function render() {
		$html = $this->prepend;
		$html .= '<'.$this->tagName;
		foreach($this->attributes as $name => $value) {
			$html .= ' '.$name;
			if($value !== true) {
				$html .= '="'.htmlspecialchars($value,ENT_COMPAT|ENT_HTML5,'ISO-8859-1').'"';
			}
		}
		if($this->classes) {
			$html .= ' class="'.htmlspecialchars(implode(' ',$this->classes),ENT_COMPAT|ENT_HTML5,'ISO-8859-1').'"';
		}
		if($this->selfClosing === true && empty($this->content)) {
			$html .= ' />';
		} else {
			$html .= '>'.$this->content.'</'.$this->tagName.'>';
		}
		$html .= $this->append;
		return $html;
	}
}
