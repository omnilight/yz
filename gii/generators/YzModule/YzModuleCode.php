<?php

class YzModuleCode extends CCodeModel
{
    /**
     * @var int
     */
    public $moduleID;
    /**
     * @var string
     */
    public $moduleVersion = '0.1';
    /**
     * @var string
     */
    public $moduleName;
    /**
     * @var string
     */
    public $moduleDescription = 'Yz Module';
    /**
     * @var string
     */
    public $moduleAuthor = 'Yz Core Team';
    /**
     * @var string
     */
    public $moduleAuthorEmail = null;
    /**
     * @var string
     */
    public $moduleUrl = null;
    /**
     * @var string
     */
    public $moduleIcon = null;

	public function rules()
	{
		return array_merge(parent::rules(), array(
			array('moduleID', 'filter', 'filter'=>'trim'),
			array('moduleID, moduleName, moduleVersion', 'required'),
			array('moduleID', 'match', 'pattern'=>'/^\w+$/', 'message'=>'{attribute} should only contain word characters.'),
            array('moduleAuthorEmail', 'email'),
            array('moduleID, moduleVersion, moduleName, moduleDescription', 'sticky'),
            array('moduleAuthor, moduleAuthorEmail, moduleUrl, moduleIcon', 'sticky'),
		));
	}

	public function attributeLabels()
	{
		return array_merge(parent::attributeLabels(), array(
			'moduleID'=>'Module ID',
            'moduleName'=>'Module name',
            'moduleDescription'=>'Module description',
            'moduleAuthor'=>'Author',
            'moduleAuthorEmail'=>'Author\'s email',
            'moduleUrl'=>'Module\'s website url',
            'moduleIcon'=>'Icon to use in admin panel',
		));
	}

	public function successMessage()
	{
		if(Yii::app()->hasModule($this->moduleID))
			return 'The module has been generated successfully. You may '.CHtml::link('try it now', Yii::app()->createUrl($this->moduleID), array('target'=>'_blank')).'.';

		$output=<<<EOD
<p>The module has been generated successfully.</p>
<p>To access the module, you need to modify the application configuration as follows:</p>
EOD;
		$code=<<<EOD
<?php
return array(
    'modules'=>array(
        '{$this->moduleID}',
    ),
    ......
);
EOD;

		return $output.highlight_string($code,true);
	}

	public function prepare()
	{
		$this->files=array();
		$templatePath=$this->templatePath;
		$modulePath=$this->modulePath;
		$moduleTemplateFile=$templatePath.DIRECTORY_SEPARATOR.'module.php';

		$this->files[]=new CCodeFile(
			$modulePath.'/'.$this->moduleClass.'.php',
			$this->render($moduleTemplateFile)
		);

		$files=CFileHelper::findFiles($templatePath,array(
			'exclude'=>array(
				'.svn',
				'.gitignore'
			),
		));

		foreach($files as $file)
		{
			if($file!==$moduleTemplateFile)
			{
				if(CFileHelper::getExtension($file)==='php')
					$content=$this->render($file);
				else if(basename($file)==='.yii')  // an empty directory
				{
					$file=dirname($file);
					$content=null;
				}
				else
					$content=file_get_contents($file);
				$this->files[]=new CCodeFile(
					$modulePath.substr($file,strlen($templatePath)),
					$content
				);
			}
		}
	}

	public function getModuleClass()
	{
		return ucfirst($this->moduleID).'Module';
	}

	public function getModulePath()
	{
		return Yii::app()->modulePath.DIRECTORY_SEPARATOR.$this->moduleID;
	}
}