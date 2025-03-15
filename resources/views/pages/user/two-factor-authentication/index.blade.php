<?php

use function Laravel\Folio\{middleware, name};
use Livewire\Volt\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use PragmaRX\Google2FA\Google2FA;
use Devdojo\Auth\Actions\TwoFactorAuth\DisableTwoFactorAuthentication;
use Devdojo\Auth\Actions\TwoFactorAuth\GenerateNewRecoveryCodes;
use Devdojo\Auth\Actions\TwoFactorAuth\GenerateQrCodeAndSecretKey;

name('user.two-factor-authentication');
middleware(['auth', 'verified', 'two-factor-enabled']);

new class extends Component
{
    public $enabled = false;

    // confirmed means that it has been enabled and the user has confirmed a code
    public $confirmed = false;

    public $showRecoveryCodes = true;

    #[Validate('required|min:6')]
    public $auth_code;

    public $secret = '';
    public $codes = '';
    public $qr = '';

    public function mount()
    {
        if (is_null(auth()->user()->two_factor_confirmed_at)) {
            app(DisableTwoFactorAuthentication::class)(auth()->user());
        } else {
            $this->confirmed = true;
        }
    }

    public function enable()
    {

        $QrCodeAndSecret = new GenerateQrCodeAndSecretKey();
        [$this->qr, $this->secret] = $QrCodeAndSecret(auth()->user());

        auth()->user()->forceFill([
            'two_factor_secret' => encrypt($this->secret),
            'two_factor_recovery_codes' => encrypt(json_encode($this->generateCodes()))
        ])->save();

        $this->enabled = true;
    }

    private function generateCodes()
    {
        $generateCodesFor = new GenerateNewRecoveryCodes();
        return $generateCodesFor(auth()->user());
    }

    public function regenerateCodes()
    {
        auth()->user()->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode($this->generateCodes()))
        ])->save();
    }

    public function cancelTwoFactor()
    {
        auth()->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null
        ])->save();

        $this->enabled = false;
    }

    #[On('submitCode')]
    public function submitCode($code)
    {
        $this->auth_code = $code;
        $this->validate();

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($this->secret, $code);

        if ($valid) {
            auth()->user()->forceFill([
                'two_factor_confirmed_at' => now(),
            ])->save();

            $this->confirmed = true;
        } else {
            $this->addError('auth_code', 'Invalid authentication code. Please try again.');
        }
    }

    public function disable()
    {
        $disable = new DisableTwoFactorAuthentication;
        $disable(auth()->user());

        $this->enabled = false;
        $this->confirmed = false;
        $this->showRecoveryCodes = true;
    }
}

?>

<x-auth::layouts.empty title="{{__('auth.twoFactorChallenge.page_title')}}">
    @volt('user.two-factor-authentication')
    <section class="flex @container justify-center items-center w-screen h-screen">

        <div x-data x-on:code-input-complete.window="$dispatch('submitCode', [event.detail.code])" class="flex flex-col w-full max-w-sm mx-auto text-sm">
            @if($confirmed)
            <div class="flex flex-col space-y-5">
                <h2 class="text-xl">{{__('auth.twoFactorChallenge.enabled')}}</h2>
                <p>{{__('auth.twoFactorChallenge.enabled_info')}}</p>
                @if($showRecoveryCodes)
                <div class="relative">
                    <p class="font-medium">{{__('auth.twoFactorChallenge.recovery_codes_info')}}</p>
                    <div class="grid max-w-xl gap-1 px-4 py-4 mt-4 font-mono text-sm bg-gray-100 rounded-lg dark:bg-gray-900 dark:text-gray-100">

                        @foreach (json_decode(decrypt(auth()->user()->two_factor_recovery_codes), true) as $code)
                        <div>{{ $code }}</div>
                        @endforeach
                    </div>
                </div>
                @endif
                <div class="flex items-center space-x-5">
                    <x-auth::elements.button type="primary" wire:click="regenerateCodes" rounded="md" size="md">{{_+_('regenerate_codes')}}</x-auth::elements.button>
                    <x-auth::elements.button type="danger" wire:click="disable" size="md" rounded="md">{{__('Disable')}} 2FA</x-auth::elements.button>
                </div>
            </div>

            @else
            @if(!$enabled)
            <div class="relative flex flex-col items-start justify-start space-y-5">
                <h2 class="text-lg font-semibold">{{__('auth.twoFactorChallenge.disabled')}}</h2>
                <p class="-translate-y-1">{{__('auth.twoFactorChallenge.info_before_enable')}}</p>
                <div class="relative w-auto">
                    <x-auth::elements.button type="primary" data-auth="enable-button" rounded="md" size="md" wire:click="enable" wire:target="enable">{{__('Enable')}}</x-auth>
                </div>
            </div>
            @else
            <div class="relative w-full space-y-5">
                <div class="space-y-5">
                    <h2 class="text-lg font-semibold">{{__('auth.twoFactorChallenge.finish_enabling')}}</h2>
                    <p>{{__('auth.twoFactorChallenge.finish_enabling_google')}}</p>
                    <p class="font-bold">{{__('auth.twoFactorChallenge.finish_enabling_instructions')}}</p>
                </div>

                <div class="relative max-w-full mx-auto overflow-hidden border rounded-lg border-zinc-200">
                    <img src="data:image/png;base64, {{ $qr }}" style="width:400px; height:auto" />
                </div>

                <p class="font-semibold text-center">
                    {{ __('Setup Key') }}: {{ $secret }}
                </p>

                <x-auth::elements.input-code id="auth-input-code" digits="6" eventCallback="code-input-complete" type="text" label="Code" />
                @error('auth_code')
                <p class="my-2 text-sm text-red-600">{{ $message }}</p>
                @enderror

                <div class="flex items-center space-x-5">
                    <x-auth::elements.button type="secondary" size="md" rounded="md" wire:click="cancelTwoFactor" wire:target="cancelTwoFactor">{{__('Cancel')}}</x-auth::elements.button>
                    <x-auth::elements.button type="primary" size="md" wire:click="submitCode(document.getElementById('auth-input-code').value)" wire:target="submitCode" rounded="md">{{__('Confirm')}}</x-auth::elements.button>
                </div>

            </div>
            @endif
            @endif
        </div>
    </section>
    @endvolt

</x-auth::layouts.empty>