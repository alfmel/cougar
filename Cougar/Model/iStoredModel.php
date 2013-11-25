<?php

namespace Cougar\Model;

/**
 * Stored Models are the old name for Persistent Models. This interface extends
 * iPersistentModel and provides backward-compatibility.
 *
 * @history
 * 2013.09.30:
 *   (AT)  Initial release
 * 2013.11.25:
 *   (AT)  Renamed to Persistent Model; old name is now deprecated
 *
 * @version 2013.09.30
 * @package Cougar
 * @license MIT
 *
 * @deprecated
 *
 * @copyright 2013 Brigham Young University
 *
 * @author (AT) Alberto Trevino, Brigham Young Univ. <alberto@byu.edu>
 */
interface iStoredModel extends iPersistentModel { }
?>
