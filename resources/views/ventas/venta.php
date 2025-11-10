// app/Models/Venta.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $fillable = ['pedido_id', 'total', 'fecha'];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}
