<?php

namespace app\modules\search\models;

/**
 * ActiveRecord for table "domain_prefix"
 *
 * @property integer $id
 * @property string $title
 */
class DomainPrefix extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     * @internal
     */
    public static function tableName()
    {
        return 'domain_prefix';
    }
}
