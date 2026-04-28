<script setup>
import { onMounted, reactive, ref, computed } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router= useRouter();

const credentials = reactive({
  phone: null,
  login_code: null
});

const waitingOnVerification = ref(false);

onMounted(() => {
  if (localStorage.getItem('token')) {
    router.push({
      name:'landing'
    })
  }
});

const formattedCredentials = computed(() => {
  return {
    phone: credentials.phone ? credentials.phone.replace(/[^0-9]/g, '').replace(/^251/, '0') : null,
    login_code: credentials.login_code
  }
})
const handleLogin =() =>{
  axios.post('http://127.0.0.1:8000/api/login', 
     formattedCredentials.value
  )
  .then(response => {
    console.log(response.data);
    waitingOnVerification.value = true

  })
  .catch(error => {
  if (error.response) {
    console.log(error.response.data)
  } else {
    console.log(error.message) // network error
  }
})
}

const handleVerification =() =>{
  axios.post('http://127.0.0.1:8000/api/verify', 
   formattedCredentials.value
  )
  .then(response => {
    console.log(response.data);
    localStorage.setItem('token', response.data.token);
    router.push({
      name:'landing'
    })
  })
  .catch(error => {
    if (error.response) {
      console.log(error.response.data);
    } else {
      console.log(error.message); // network error
    }
  });


}

</script>

<template>
  <div class="min-h-screen flex items-center justify-center">
    <div class="w-full max-w-sm">
      <h1 class="text-3xl font-semibold mb-4">
        {{ waitingOnVerification ? 'Enter your verification code' : 'Enter your phone number' }}
      </h1>

      <form v-if="!waitingOnVerification" @submit.prevent="handleLogin">
        <div class="overflow-hidden shadow sm:rounded-md text-left">
          <div class="bg-white px-4 py-5 sm:p-6">
            <input
              type="text"
              name="phone"
              id="phone"
              placeholder="+251 91 234 5678"
              class="mt-1 block w-full px-3 py-2 rounded-md border border-gray-300 shadow-sm"
              v-maska
              data-maska="+251 ## ### ####"
              v-model="credentials.phone"
            />
          </div>

          <div class="bg-gray-50 px-4 py-3 text-right sm:px-6">
            <button
              type="submit"
              class="inline-flex justify-center rounded-md border border-transparent bg-black py-2 px-4 text-white"
            >
              Continue
            </button>
          </div>
        </div>
      </form>

      <form v-else @submit.prevent="handleVerification">
        <div class="overflow-hidden shadow sm:rounded-md text-left">
          <div class="bg-white px-4 py-5 sm:p-6">
            <input
              type="text"
              name="code"
              id="code"
              placeholder="Enter 6-digit code"
              class="mt-1 block w-full px-3 py-2 rounded-md border border-gray-300 shadow-sm"
              v-maska
              data-maska="######"
              v-model="credentials.login_code"
            />
          </div>

          <div class="bg-gray-50 px-4 py-3 text-right sm:px-6">
            <button
              type="submit"
              class="inline-flex justify-center rounded-md border border-transparent bg-black py-2 px-4 text-white"
            >
              Verify
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>