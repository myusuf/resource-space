<?php
function HookMediaapiSearch_AdvancedAdvsearchaddfields()
{
    echo '
        <div class="Question">
		<label>Has not been published to mediaapi</label>
		<input type="radio" class="SearchTypeCheckbox" name="mediaapi_canbepublished" id="mediaapi_canbepublished" onChange="UpdateResultCount();" value="Y"> Yes
        <input type="radio" class="SearchTypeCheckbox" name="mediaapi_canbepublished" id="mediaapi_canbepublished" onChange="UpdateResultCount();" value="N" checked="checked"> No
        </div>
    ';
}
