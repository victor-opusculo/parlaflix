<?php
namespace VictorOpusculo\Parlaflix\Lib\Model\Settings;

use VOpus\PhpOrm\DataEntity;
use VOpus\PhpOrm\DataProperty;

class CertificateBackground2MediaId extends DataEntity
{
    public function __construct($initialValues = null)
    {
        $this->properties = (object)
        [
            'name' => new DataProperty(null, fn() => 'CERT_BG_2_MEDIA_ID', DataProperty::MYSQL_STRING),
            'value' => new DataProperty(null, fn() => '', DataProperty::MYSQL_STRING)
        ];

        parent::__construct($initialValues);

        $this->properties->name->setValue('CERT_BG_2_MEDIA_ID');

    }

    protected string $databaseTable = 'settings';
    protected string $formFieldPrefixName = 'certBg2MediaId';
    protected array $primaryKeys = ['name'];
    protected array $setPrimaryKeysValue = ['name'];
}