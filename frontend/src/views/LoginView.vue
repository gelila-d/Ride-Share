<script setup>
import { reactive} from 'vue';
import { useRouter } from 'vue-router';

const data = reactive({
  phone: null
});
const handleLogin =() =>{
  console.log('Login button clicked');
  console.log(data.phone);
}

</script>

<template>
  <div class="min-h-screen flex items-center justify-center">
    <div class="w-full max-w-sm">
      <h1 class="text-3xl font-semibold mb-4">Enter your phone number</h1>

      <form action="#" @submit.prevent="handleLogin">
        <div class="overflow-hidden shadow sm:rounded-md text-left">
          <div class="bg-white px-4 py-5 sm:p-6">
            <input
              type="text"
              name="phone"
              id="phone"
              placeholder="+251 91 234 5678"
              class="mt-1 block w-full px-3 py-2 rounded-md border border-gray-300 shadow-sm"
              v-maska data-maska="+251 ## ### ####"
              v-model="data.phone"
            />
          </div>

          <div class="bg-gray-50 px-4 py-3 text-right sm:px-6">
            <button @submit="handleLogin"
              type="submit"
              class="inline-flex justify-center rounded-md border border-transparent bg-black py-2 px-4 text-white"
            >
              Continue
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>