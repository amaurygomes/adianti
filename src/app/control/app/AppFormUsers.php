<?php

class AppFormUsers extends TPage
{
    protected $form;
    protected $program_list;
    protected $unit_list;
    protected $group_list;
    protected $role_list;
    
    public function __construct()
    {
        parent::__construct();
        
        parent::setTargetContainer('adianti_right_panel');
        
        // Criação do formulário
        $this->form = new BootstrapFormBuilder('form_System_user');
        $this->form->setFormTitle( _t('User') );
        $this->form->enableClientValidation();
        
        // Criação dos campos ORIGINAIS
        $id            = new TEntry('id');
        $name          = new TEntry('name');
        $login         = new TEntry('login');
        $password      = new TPassword('password');
        $repassword    = new TPassword('repassword');
        $email         = new TEntry('email');
        $unit_id       = new TDBCombo('system_unit_id','permission','SystemUnit','id','name');
        $frontpage_id  = new TDBUniqueSearch('frontpage_id', 'permission', 'SystemProgram', 'id', 'name', 'name');
        $phone         = new TEntry('phone');
        $address       = new TEntry('address');
        $function_name = new TEntry('function_name');
        $about         = new TEntry('about');
        $custom_code   = new TEntry('custom_code');
        
        // Campos ADICIONADOS
        $cpf           = new TEntry('cpf');
        $vtr           = new TEntry('vtr');
        
        // Configurações
        $password->disableAutoComplete();
        $repassword->disableAutoComplete();
        
        $btn = $this->form->addAction( _t('Save'), new TAction(array($this, 'onSave')), 'far:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink( _t('Clear'), new TAction(array($this, 'onEdit')), 'fa:eraser red');
        
        // Tamanhos
        $id->setSize('50%');
        $name->setSize('100%');
        // ... (outros setSize)
        $cpf->setSize('100%');
        $cpf->setMask('999.999.999-99');
        $vtr->setSize('100%');
        $vtr->setMask('999999');
        $id->setEditable(false);
        
        // Validações
        $name->addValidation(_t('Name'), new TRequiredValidator);
        $login->addValidation('Login', new TRequiredValidator);
        $email->addValidation('Email', new TEmailValidator);
        
        // Validações CPF e VTR
        $cpf->addValidation('CPF', new TRequiredValidator);
        $vtr->addValidation('VTR', new TRequiredValidator);
        $vtr->addValidation('VTR', new TNumericValidator);
        
        // Adição dos campos ao formulário (na ordem desejada)
        $this->form->addFields( [new TLabel('ID')], [$id],  [new TLabel(_t('Name'))], [$name] );
        $this->form->addFields( [new TLabel('CPF')], [$cpf],  [new TLabel('VTR')], [$vtr] ); 
        $this->form->addFields( [new TLabel(_t('Login'))], [$login],  [new TLabel(_t('Email'))], [$email] );
        $this->form->addFields( [new TLabel(_t('Address'))], [$address],  [new TLabel(_t('Phone'))], [$phone] );
        $this->form->addFields( [new TLabel(_t('Function'))], [$function_name],  [new TLabel(_t('About'))], [$about] );
        $this->form->addFields( [new TLabel(_t('Main unit'))], [$unit_id],  [new TLabel(_t('Front page'))], [$frontpage_id] );
        $this->form->addFields( [new TLabel(_t('Password'))], [$password],  [new TLabel(_t('Password confirmation'))], [$repassword] );
        $this->form->addFields( [new TLabel(_t('Custom code'))], [$custom_code] );
        
        // Subformulários (Grupos, Unidades, etc.)
        // ... (todo o código do subformulário copiado) ...
        
        $subform = new BootstrapFormBuilder;
        $subform->setFieldSizes('100%');
        $subform->setProperty('style', 'border:none');
        
        $subform->appendPage( _t('Groups') );
        $this->group_list = new TDBCheckList('group_list', 'permission', 'SystemGroup', 'id', 'name');
        $this->group_list->makeScrollable();
        $this->group_list->setHeight(210);
        $subform->addFields( [$this->group_list] );
        
        $subform->appendPage( _t('Units') );
        $this->unit_list = new TDBCheckList('unit_list', 'permission', 'SystemUnit', 'id', 'name');
        $this->unit_list->makeScrollable();
        $this->unit_list->setHeight(210);
        $subform->addFields( [$this->unit_list] );
        
        $subform->appendPage( _t('Roles') );
        $this->role_list = new TDBCheckList('role_list', 'permission', 'SystemRole', 'id', 'name');
        $this->role_list->makeScrollable();
        $this->role_list->setHeight(210);
        $subform->addFields( [$this->role_list] );
        
        $subform->appendPage( _t('Programs') );
        $this->program_list = new TCheckList('program_list');
        $this->program_list->setIdColumn('id');
        $this->program_list->addColumn('id',    'ID',    'center',  '10%');
        $col_name    = $this->program_list->addColumn('name', _t('Name'),    'left',   '50%');
        $col_program = $this->program_list->addColumn('controller', _t('Menu path'),    'left',   '40%');
        $col_program->enableAutoHide(500);
        $this->program_list->setHeight(150);
        $this->program_list->makeScrollable();
        $subform->addFields( [$this->program_list] );
        
        $this->form->addContent([$subform]);
        
        $col_name->enableSearch();
        $search_name = $col_name->getInputSearch();
        $search_name->placeholder = _t('Search');
        $search_name->style = 'width:50%;margin-left: 4px; display:inline';
        
        $col_program->setTransformer( function($value, $object, $row) {
            $menuparser = new TMenuParser('menu.xml');
            $paths = $menuparser->getPath($value);
            
            if ($paths)
            {
                return implode(' &raquo; ', $paths);
            }
        });
        
        TTransaction::open('permission');
        $this->program_list->addItems( SystemProgram::get() );
        TTransaction::close();
        
        $this->form->addHeaderActionLink(_t('Close'), new TAction([$this, 'onClose']), 'fa:times red');
        
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add($this->form);

        parent::add($container);
    }

    public function onSave($param)
    {
        // Lógica onSave COPIADA e ADAPTADA (USANDO SystemUserExtended)
        $ini  = AdiantiApplicationConfig::get();
        
        try
        {
            TTransaction::open('permission');
            
            $data = $this->form->getData();
            $this->form->setData($data);
            
            $object = new SystemUserExtended( (int) $data->id ); 
            $object->fromArray( (array) $data );
            
            // ... (Restante da lógica onSave, usando SystemUserExtended para buscas e salvamento) ...
            
            unset($object->accepted_term_policy);

            $senha = $object->password;
            
            if( empty($object->login) )
            {
                throw new Exception(TAdiantiCoreTranslator::translate('The field ^1 is required', _t('Login')));
            }
            
            if( empty($object->id) )
            {
                if (SystemUserExtended::newFromLogin($object->login) instanceof SystemUserExtended)
                {
                    throw new Exception(_t('An user with this login is already registered'));
                }
                
                if (SystemUserExtended::newFromEmail($object->email) instanceof SystemUserExtended)
                {
                    throw new Exception(_t('An user with this e-mail is already registered'));
                }

                if (SystemUserExtended::newFromCpf($object->cpf) instanceof SystemUserExtended)
                {
                    throw new Exception('Um usuário com este CPF já está registrado');
                }
                
                if (SystemUserExtended::newFromVtr($object->vtr) instanceof SystemUserExtended)
                {
                    throw new Exception('Um usuário com esta VTR já está registrado');
                }
                
                if ( empty($object->password) )
                {
                    throw new Exception(TAdiantiCoreTranslator::translate('The field ^1 is required', _t('Password')));
                }
                
                $object->active = 'Y';
            }
            else
            {
                if (SystemUserExtended::where('login', '=', $object->login)->where('id', '<>', $object->id)->first() instanceof SystemUserExtended)
                {
                    throw new Exception(_t('An user with this login is already registered'));
                }
                
                if (SystemUserExtended::where('cpf', '=', $object->cpf)->where('id', '<>', $object->id)->first() instanceof SystemUserExtended)
                {
                    throw new Exception('Um usuário com este CPF já está registrado');
                }
                
                if (SystemUserExtended::where('vtr', '=', $object->vtr)->where('id', '<>', $object->id)->first() instanceof SystemUserExtended)
                {
                    throw new Exception('Um usuário com esta VTR já está registrado');
                }
            }
            
            if ( $object->password )
            {
                if (isset($ini['general']['validate_strong_pass']) && $ini['general']['validate_strong_pass'] == '1')
                {
                    (new TStrongPasswordValidator)->validate(_t('Password'), $object->password);
                }
                
                if( $object->password !== $param['repassword'] )
                {
                    throw new Exception(_t('The passwords do not match'));
                }
                
                $object->password = SystemUser::passwordHash($object->password);

                if ($object->id)
                {
                    SystemUserOldPassword::validate($object->id, $object->password);
                }
            }
            else
            {
                unset($object->password);
            }
            
            $object->store();

            if ($object->password)
            {
                SystemUserOldPassword::register($object->id, $object->password);
            }
            $object->clearParts();
            
            if( !empty($data->group_list) )
            {
                foreach( $data->group_list as $group_id )
                {
                    $object->addSystemUserGroup( new SystemGroup($group_id) );
                }
            }
            
            if( !empty($data->unit_list) )
            {
                foreach( $data->unit_list as $unit_id )
                {
                    $object->addSystemUserUnit( new SystemUnit($unit_id) );
                }
            }
            
            if (!empty($data->program_list))
            {
                foreach ($data->program_list as $program_id)
                {
                    $object->addSystemUserProgram( new SystemProgram( $program_id ) );
                }
            }
            
            if( !empty($data->role_list) )
            {
                foreach( $data->role_list as $role_id )
                {
                    $object->addSystemUserRole( new SystemRole($role_id) );
                }
            }
            
            $data = new stdClass;
            $data->id = $object->id;
            TForm::sendData('form_System_user', $data);
            
            TTransaction::close();
            
            $pos_action = new TAction(['SystemUserList', 'onReload']);
            
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'), $pos_action);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    public function onEdit($param)
    {
        // Lógica onEdit COPIADA e ADAPTADA (USANDO SystemUserExtended)
        try
        {
            if (isset($param['key']))
            {
                $key=$param['key'];
                TTransaction::open('permission');
                
                $object = new SystemUserExtended($key); 
                
                unset($object->password);
                
                // ... (lógica de carregamento de grupos, unidades, etc.) ...
                
                $groups = array();
                $units  = array();
                
                if( $groups_db = $object->getSystemUserGroups() )
                {
                    foreach( $groups_db as $group )
                    {
                        $groups[] = $group->id;
                    }
                }
                
                if( $units_db = $object->getSystemUserUnits() )
                {
                    foreach( $units_db as $unit )
                    {
                        $units[] = $unit->id;
                    }
                }
                
                $program_ids = array();
                foreach ($object->getSystemUserPrograms() as $program)
                {
                    $program_ids[] = $program->id;
                }
                
                $role_ids = array();
                foreach ($object->getSystemUserRoles() as $role)
                {
                    $role_ids[] = $role->id;
                }
                
                $object->program_list = $program_ids;
                $object->group_list   = $groups;
                $object->unit_list    = $units;
                $object->role_list    = $role_ids;
                
                $this->form->setData($object);
                TTransaction::close();
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
}