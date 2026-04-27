<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const form = reactive({ email: '', password: '' });
const loading = ref(false);
const errorMessage = ref('');

const handleLogin = async () => {
  if (!form.email || !form.password) {
    errorMessage.value = "Email dan Password wajib diisi!";
    return;
  }

  loading.value = true;
  errorMessage.value = '';
  
  try {
    const response = await axios.post('http://localhost:8000/api/login', form);
    
    // Simpan token ke localStorage
    localStorage.setItem('token', response.data.data.token);
    
    // Arahkan ke dashboard setelah login sukses
    await router.push('/dashboard');
  } catch (error) {
    // Menampilkan pesan error dari Laravel jika ada
    errorMessage.value = error.response?.data?.message || 'Login Gagal. Cek koneksi API Laravel kamu.';
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="min-h-screen bg-gray-100 flex flex-col font-sans relative overflow-hidden">
    <header class="bg-white border-b-4 border-green-700 shadow-md p-5 z-10">
      <div class="max-w-7xl mx-auto flex items-center justify-between">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-green-50 rounded-lg border border-green-200 flex items-center justify-center text-[8px] font-bold text-green-800 text-center uppercase">
            Logo Pemprov
          </div>
          <div>
            <h1 class="text-lg font-black text-green-950 leading-none uppercase tracking-tighter">Badan Kepegawaian Daerah</h1>
            <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mt-1">Provinsi Jawa Tengah</p>
          </div>
        </div>
        <div class="text-right hidden sm:block">
          <p class="text-[10px] font-black text-gray-300 uppercase italic">Aplikasi Manajemen Kepegawaian</p>
          <p class="text-xs font-black text-green-800 tracking-widest">SINAGA v1.0</p>
        </div>
      </div>
    </header>

    <main class="flex-grow flex items-center justify-center p-6 relative">
      <div class="absolute inset-0 flex items-center justify-center opacity-[0.03] pointer-events-none select-none">
        <h1 class="text-[25vw] font-black text-green-900 -rotate-12 uppercase">SINAGA</h1>
      </div>

      <div class="w-full max-w-md bg-white p-10 rounded-3xl shadow-2xl border border-gray-100 z-10 relative transition-all">
        <div class="text-center mb-10">
          <div class="inline-block bg-green-100 text-green-700 text-[10px] font-black px-4 py-1 rounded-full uppercase tracking-widest mb-4">
            Akses Administrator
          </div>
          <h2 class="text-3xl font-black text-gray-900 tracking-tight italic">Selamat Datang</h2>
          <p class="text-sm text-gray-400 mt-2 font-medium">Silakan login untuk akses Dashboard</p>
        </div>

        <form @submit.prevent="handleLogin" class="space-y-6">
          <div>
            <label class="block mb-2 text-[10px] font-black text-gray-500 uppercase tracking-widest">Email Kedinasan</label>
            <input 
              v-model="form.email" 
              type="email" 
              class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-green-100 focus:border-green-600 outline-none transition-all text-sm font-medium"
              placeholder="nama@jatengprov.go.id" required>
          </div>
          
          <div>
            <label class="block mb-2 text-[10px] font-black text-gray-500 uppercase tracking-widest">Kata Sandi</label>
            <input 
              v-model="form.password" 
              type="password" 
              class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-green-100 focus:border-green-600 outline-none transition-all text-sm font-medium"
              placeholder="••••••••" required>
          </div>

          <button 
            type="submit" :disabled="loading" 
            class="w-full py-4 bg-green-700 hover:bg-green-800 text-white font-black rounded-2xl shadow-xl transition-all transform active:scale-[0.98] disabled:opacity-50 uppercase tracking-widest text-xs">
            {{ loading ? 'Memverifikasi...' : 'Masuk ke Sistem' }}
          </button>

          <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-100 rounded-xl">
             <p class="text-[11px] text-red-600 text-center font-bold italic">"{{ errorMessage }}"</p>
          </div>
        </form>
      </div>
    </main>

    <footer class="p-8 text-center bg-white border-t border-gray-100">
      <p class="text-[9px] text-gray-400 font-bold uppercase tracking-[0.4em]">
        &copy; 2024 Pemerintah Provinsi Jawa Tengah | BKD JATENG
      </p>
    </footer>
  </div>
</template>