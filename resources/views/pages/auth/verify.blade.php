<?php

use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;
use Devdojo\Auth\Traits\HasConfigs;
use function Laravel\Folio\{middleware, name};
use Livewire\Volt\Component;

if (!isset($_GET['preview']) || (isset($_GET['preview']) && $_GET['preview'] != true) || !app()->isLocal()) {
    middleware(['guest']);
}

//middleware(['auth', 'throttle:6,1']);
name('verification.notice');

new class extends Component
{
    use HasConfigs;

    public function mount()
    {
        $this->loadConfigs();
    }

    public function resend()
    {
        $user = auth()->user();
        if ($user->hasVerifiedEmail()) {
            redirect('/');
        }

        $user->sendEmailVerificationNotification();

        event(new Verified($user));

        $this->dispatch('resent');
        session()->flash('resent');
    }
};

?>

<x-auth::layouts.app title="{{__('Verify Email Address')}}">

    @volt('auth.verify')
    <x-auth::elements.container>

        <x-auth::elements.heading
            :text="__('Verify Your Email Address')"
            :description="__('auth.verify.subheadline')"
            :show_subheadline="($settings->verify_show_subheadline ?? false)" />


        @if (session('resent'))
        <div class="flex items-start px-4 py-3 mb-5 text-sm text-white bg-green-500 rounded shadow" role="alert">
            <svg class="mr-2 w-5 h-5 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>

            <p>{{__('auth.verify.new_link_sent')}}</p>
        </div>
        @endif

        <div class="text-sm leading-6 text-gray-700 dark:text-gray-400">
            <p>{{__('Please click the button below to verify your email address.')}} <a wire:click="resend" data-auth="verify-email-resend-link" class="text-gray-700 underline transition duration-150 ease-in-out cursor-pointer dark:text-gray-300 hover:text-gray-600 focus:outline-none focus:underline">{{__('auth.verify.new_request_link')}}</a></p>
        </div>


        <div class="mt-2 space-x-0.5 text-sm leading-5 text-center text-gray-600 translate-y-4 dark:text-gray-400">
            <span>{{__('or')}}</span>
            <button onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-gray-500 underline cursor-pointer dark:text-gray-400 dark:hover:text-gray-300 hover:text-gray-800">
                {{__('Logout')}}
            </button>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>

    </x-auth::elements.container>
    @endvolt

</x-auth::layouts.app>
