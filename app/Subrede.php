<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subrede extends Model
{
    public $timestamps = false;

    protected $fillable = [
        "endereco", "cidr", "tipo_subrede_id", "descricao", "ignorar_broadcast", "ignorar_gateway",
    ];

    protected $hidden = [
        "id", "tipo_subrede_id",
    ];

    public function tipo() {
        return $this->belongsTo('App\TipoSubrede', 'tipo_subrede_id', 'id');
    }

    /**
     * Obtém o primeiro e último endereço da subrede.
     * @param Subrede $subrede Instância de uma subrede
     * @return array Array contendo o primeiro e último endereço da rede
     */
    public function getRange() {
        $maskBinStr =str_repeat("1", $this->cidr) . str_repeat("0", 32 - $this->cidr); // Máscara da rede em binário
        $inverseMaskBinStr = str_repeat("0", $this->cidr) . str_repeat("1", 32 - $this->cidr); // Inverso da Máscara da rede em binário

        $ipLong = ip2long($this->endereco); // Endereço da rede convertido em long integer

        $ipMaskLong = bindec($maskBinStr);
        $inverseIpMaskLong = bindec($inverseMaskBinStr);

        $netWork = $ipLong & $ipMaskLong;

        $start = $netWork;
        if(!$this->ignorar_gateway) ++$start; // Não ignora o endereço de

        $end = ($netWork | $inverseIpMaskLong);
        if(!$this->ignorar_broadcast) --$end; // Não ignora o endereço de broadcast

        return array('firstIP' => $start, 'lastIP' => $end );
    }

    /**
     * Obtém todos os endereços possíveis de uma subrede.
     * @param Subrede $subrede Instância da subrede as quais os IPs serão obtidos
     * @return array Array contendo os IPs possíveis para a subrede.
     */
    public function getAllIps() {
        $ips = array();

        $range = $this->getRange();

        for ($ip = $range['firstIP']; $ip <= $range['lastIP']; $ip++) $ips[] = long2ip($ip);

        return $ips;
    }
}
