<?php
/**
 * Created by PhpStorm.
 * User: bw_dev
 * Date: 10.12.2018
 * Time: 18:47
 */

namespace yozh\base\components;

use yozh\base\traits\ArrayableTrait;

class Arrayable extends Component implements \ArrayAccess, \JsonSerializable
{
	use ArrayableTrait;
}