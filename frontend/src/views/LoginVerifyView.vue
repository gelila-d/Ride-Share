<script setup>
import { reactive } from 'vue';
import axios from 'axios';
import { useRouter, useRoute } from 'vue-router';

const router = useRouter();
const route = useRoute();

const credentials = reactive({
  login_code: null
});

const handleVerification = () => {
  const phoneVal = route.query.phone;
  if (!phoneVal) {
    router.push({ name: 'login' });
    return;
  }
  
  const codeVal = credentials.login_code || '';
  const formattedCode = codeVal.replace(/[^0-9]/g, '');

  axios.post('http://127.0.0.1:8000/api/login/verify', {
    phone: phoneVal,
    login_code: formattedCode
  })
  .then(response => {
    console.log(response.data);
    localStorage.setItem('token', response.data.token);
    alert("Login Successful! You are authenticated.");
  })
  .catch(error => {
    if (error.response) {
      console.log(error.response.data);
      alert(error.response.data.message || "Invalid code");
    } else {
      console.log(error.message); // network error
    }
  });
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center">
    <div class="w-full max-w-sm">
      <h1 class="text-3xl font-semibold mb-4">Enter verification code</h1>
      
      <form action="#" @submit.prevent="handleVerification">
        <div class="overflow-hidden shadow sm:rounded-md text-left">
          <div class="bg-white px-4 py-5 sm:p-6">
            <p class="text-sm text-gray-500 mb-4">
              We sent a 6-digit verification code to your phone.
            </p>
            <input
              type="text"
              name="login_code"
              id="login_code"
              placeholder="123456"
              class="mt-1 block w-full px-3 py-2 rounded-md border border-gray-300 shadow-sm text-center tracking-widest text-lg"
              v-maska data-maska="######"
              v-model="credentials.login_code"
            />
          </div>
          
          <div class="bg-gray-50 px-4 py-3 text-right sm:px-6 flex justify-between items-center">
            <button
               type="button"
               @click="router.push({ name: 'login' })"
               class="text-sm text-gray-600 hover:text-black"
            >
               Back
            </button>
            <button
              type="submit"
              class="inline-flex justify-center rounded-md border border-transparent bg-black py-2 px-4 text-white"
            >
              Verify Code
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>
