<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
  <div class="w-full max-w-md space-y-8">
    
    <!-- Logo + Title -->
    <div class="text-center">
      <x-filament-panels::logo class="mx-auto h-12 w-auto mb-6" />
      <h2 class="text-4xl font-extrabold text-gray-900">{{ $this->getTitle() }}</h2>
      <p class="mt-2 text-base text-gray-500">{{ $this->getSubheading() }}</p>
    </div>
    
    <!-- Card -->
    <div class="bg-white p-8 sm:p-10 shadow-xl rounded-2xl border border-gray-200">
      <form wire:submit.prevent="verify" class="space-y-6">
        
        <!-- OTP Inputs -->
        <div class="flex justify-between space-x-2">
          @foreach(range(1,6) as $i)
            <input
              type="text"
              maxlength="1"
              aria-label="Digit {{ $i }}"
              class="w-12 h-12 text-center text-xl font-medium border border-gray-300 rounded-md
                     focus:outline-none focus:ring-2 focus:ring-primary-500 transition"
              wire:model.defer="code.{{ $i }}"
            />
          @endforeach
        </div>
        
        <!-- Primary Action -->
        <button
          type="submit"
          class="w-full py-3 text-lg font-semibold text-white bg-primary-600 rounded-md
                 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
        >
          Verify Email
        </button>
      
      </form>
      
      <!-- Divider with “Need help?” -->
      <div class="relative my-6">
        <div class="absolute inset-0 flex items-center">
          <div class="w-full border-t border-gray-200"></div>
        </div>
        <div class="relative flex justify-center text-sm">
          <span class="px-2 bg-white text-gray-400">Need help?</span>
        </div>
      </div>
      
      <!-- Secondary Actions + Countdown -->
      <div class="flex justify-between text-sm">
        <button
          type="button"
          wire:click="resend"
          @disabled($secondsRemaining > 0)
          class="font-medium 
                 {{ $secondsRemaining > 0 
                     ? 'text-gray-400 cursor-not-allowed' 
                     : 'text-primary-600 hover:underline' }}"
        >
          {{ $secondsRemaining > 0 
             ? "Resend in {$secondsRemaining}s" 
             : 'Resend Code' }}
        </button>
        <button
          type="button"
          wire:click="backToLogin"
          class="font-medium text-primary-600 hover:underline"
        >
          Back to Login
        </button>
      </div>
    </div>
    
    <!-- Footer Note -->
    <p class="text-center text-xs text-gray-500">
      Didn’t receive it? Check spam or&nbsp;
      <a href="/support" class="underline hover:text-primary-600">contact support</a>.
    </p>
  
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-advance between input fields
    const inputs = document.querySelectorAll('input[type="text"]');
    inputs.forEach((input, index) => {
        input.addEventListener('input', function(e) {
            if (e.target.value.length === 1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && e.target.value === '' && index > 0) {
                inputs[index - 1].focus();
            }
        });
    });

    // Auto-submit when all fields are filled
    inputs[inputs.length - 1].addEventListener('input', function() {
        const allFilled = Array.from(inputs).every(input => input.value.length === 1);
        if (allFilled) {
            setTimeout(() => {
                document.querySelector('form').dispatchEvent(new Event('submit', { bubbles: true }));
            }, 100);
        }
    });
});

// Handle countdown timer
window.addEventListener('start-countdown', function() {
    const countdown = setInterval(() => {
        @this.call('decrementCountdown');
        if (@this.secondsRemaining <= 0) {
            clearInterval(countdown);
        }
    }, 1000);
});
</script>
