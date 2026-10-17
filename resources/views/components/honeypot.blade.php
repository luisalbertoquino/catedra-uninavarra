{{-- Campos anti-bots (ver App\Http\Middleware\BlockBots): trampa invisible + hora de carga cifrada --}}
<div aria-hidden="true" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden">
    <label for="website">No llenes este campo</label>
    <input type="text" id="website" name="website" value="" tabindex="-1" autocomplete="off">
</div>
<input type="hidden" name="_hp_ts" value="{{ \Illuminate\Support\Facades\Crypt::encryptString((string) now()->timestamp) }}">
