<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 30.09.2018
 * Time: 10:52
 */

namespace yozh\base\traits;

trait YiiActiveRecordTrait
{
	
	/**
	 * @var array attribute values indexed by attribute names
	 */
	protected $_attributes = [];
	/**
	 * @var array|null old attribute values indexed by attribute names.
	 * This is `null` if the record [[isNewRecord|is new]].
	 */
	protected $_oldAttributes;
	/**
	 * @var array related models indexed by the relation names
	 */
	protected $_related = [];
	/**
	 * @var array relation names indexed by their link attributes
	 */
	protected $_relationsDependencies = [];
	
	public static function getDb()
	{
		return Yii::$app->getDb();
	}
	
	/**
	 * Creates an active record instance.
	 *
	 * This method is called together with [[populateRecord()]] by [[ActiveQuery]].
	 * It is not meant to be used for creating new records directly.
	 *
	 * You may override this method if the instance being created
	 * depends on the row data to be populated into the record.
	 * For example, by creating a record based on the value of a column,
	 * you may implement the so-called single-table inheritance mapping.
	 * @param array $row row data to be populated into the record.
	 * @return static the newly created active record
	 */
	public static function instantiate( $row )
	{
		return new static();
	}
	
	/**
	 * {@inheritdoc}
	 */
	public static function populateRecord( $record, $row )
	{
		$columns = static::getTableSchema()->columns;
		
		foreach( $row as $name => $value ) {
			
			if( isset( $columns[ $name ] ) ) {
				$record->_attributes[ $name ] = $columns[ $name ]->phpTypecast( $value );
			}
			else if( $record->canSetProperty( $name ) ) {
				$record->$name = $value;
			}
			
		}
		
		$record->_oldAttributes         = $record->_attributes;
		$record->_related               = [];
		$record->_relationsDependencies = [];
	}
	
	/**
	 * Declares the name of the database table associated with this AR class.
	 * By default this method returns the class name as the table name by calling [[Inflector::camel2id()]]
	 * with prefix [[Connection::tablePrefix]]. For example if [[Connection::tablePrefix]] is `tbl_`,
	 * `Customer` becomes `tbl_customer`, and `OrderItem` becomes `tbl_order_item`. You may override this method
	 * if the table is not named after this convention.
	 * @return string the table name
	 */
	public static function tableName()
	{
		return '{{%' . Inflector::camel2id(StringHelper::basename(get_called_class()), '_') . '}}';
	}
	
	/**
	 * Returns the schema information of the DB table associated with this AR class.
	 * @return TableSchema the schema information of the DB table associated with this AR class.
	 * @throws InvalidConfigException if the table for the AR class does not exist.
	 */
	public static function getTableSchema()
	{
		$tableSchema = static::getDb()
		                     ->getSchema()
		                     ->getTableSchema(static::tableName());
		
		if ($tableSchema === null) {
			throw new InvalidConfigException('The table does not exist: ' . static::tableName());
		}
		
		return $tableSchema;
	}
	
	/**
	 * Initializes the object.
	 * This method is called at the end of the constructor.
	 * The default implementation will trigger an [[EVENT_INIT]] event.
	 */
	public function init()
	{
		parent::init();
		$this->trigger(self::EVENT_INIT);
	}
	
	/**
	 * This method is called when the AR object is created and populated with the query result.
	 * The default implementation will trigger an [[EVENT_AFTER_FIND]] event.
	 * When overriding this method, make sure you call the parent implementation to ensure the
	 * event is triggered.
	 */
	public function afterFind()
	{
		$this->trigger(self::EVENT_AFTER_FIND);
	}
	
}