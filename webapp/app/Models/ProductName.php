<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Product name model.
 *
 * This represents a localized name for a purchasable product.
 *
 * @property int id
 * @property int product_id
 * @property-read Product product
 * @property string locale
 * @property string name
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class ProductName extends Model {

    protected $table = "product_names";

    protected $fillable = [
        'product_id',
        'locale',
        'name',
    ];

    /**
     * Get the relation to the product this localized name belongs to.
     *
     * @return Relation to the product this localized name belongs to.
     */
    public function product() {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the display name of the language corresponding to the name locale.
     *
     * @return string The language name.
     */
    public function languageName() {
        return __('lang.name', [], $this->locale);
    }
}
