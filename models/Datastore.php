<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "datastore".
 *
 * @property string $id
 * @property string $server_id
 * @property string $uuid
 * @property string $value
 * @property string $space
 * @property string $is_default
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Server $server
 * @property Vps[] $vps
 */
class Datastore extends \yii\db\ActiveRecord
{
    const IS_DEFAULT = 1;
    const IS_NOT_DEFAULT = 2;
    
    const VSAN_NO = 1;
    const VSAN_YES = 2;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'datastore';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['server_id', 'uuid', 'value', 'space', 'is_default', 'vsan'], 'required'],
            [['server_id', 'space', 'is_default', 'vsan', 'created_at', 'updated_at'], 'integer'],
            #[['value'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'server_id' => Yii::t('app', 'Server ID'),
            'uuid' => Yii::t('app', 'Uuid'),
            'value' => Yii::t('app', 'Value'),
            'space' => Yii::t('app', 'Space'),
            'is_default' => Yii::t('app', 'Is Default'),
            'vsan' => Yii::t('app', 'Vsan'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServer()
    {
        return $this->hasOne(Server::className(), ['id' => 'server_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVps()
    {
        return $this->hasMany(Vps::className(), ['datastore_id' => 'id']);
    }

    /**
     * @inheritdoc
     * @return \app\models\queries\DatastoreQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\queries\DatastoreQuery(get_called_class());
    }
    
    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => ['server_id', 'uuid', 'value', 'space', 'is_default', 'vsan'],
        ];
    }
    
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
    
    public static function getDefaultYesNo()
    {
        return [
            self::IS_DEFAULT => Yii::t('app', 'Yes'),
            self::IS_NOT_DEFAULT => Yii::t('app', 'No'),
        ];
    }
    
    public static function getVsanYesNo()
    {
        return [
            self::VSAN_NO => Yii::t('app', 'No'),
            self::VSAN_YES => Yii::t('app', 'Yes'),
        ];
    }

    public static function findAvailable($id)
    {
        $sql = "select c.id, c.space, COUNT(a.id) as total, SUM(IF(a.plan_id IS NOT NULL, b.hard, a.vps_hard)) as used FROM vps a LEFT JOIN plan b ON b.id = a.plan_id INNER JOIN datastore c ON c.id = a.datastore_id WHERE a.server_id = $id GROUP BY a.datastore_id";

        $datastores = Yii::$app->db->createCommand($sql)->queryAll();

        $data = [];
        
        foreach ($datastores as $store) {
        
            $data[$store['id']] = ($store['total'] * $store['used']) / $store['space'];
        }

        return array_search(min($data), $data);
    }
}
