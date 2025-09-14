<?php
// Shared CSS styles extracted from the existing code
?>
<style>
:root {
  --background: 0 0% 100%;
  --foreground: 222.2 84% 4.9%;
  --card: 0 0% 100%;
  --card-foreground: 222.2 84% 4.9%;
  --popover: 0 0% 100%;
  --popover-foreground: 222.2 84% 4.9%;
  --primary: 221.2 83.2% 53.3%;
  --primary-foreground: 210 40% 98%;
  --secondary: 210 40% 96%;
  --secondary-foreground: 222.2 84% 4.9%;
  --muted: 210 40% 96%;
  --muted-foreground: 215.4 16.3% 46.9%;
  --accent: 210 40% 96%;
  --accent-foreground: 222.2 84% 4.9%;
  --destructive: 0 84.2% 60.2%;
  --destructive-foreground: 210 40% 98%;
  --border: 214.3 31.8% 91.4%;
  --input: 214.3 31.8% 91.4%;
  --ring: 221.2 83.2% 53.3%;
  --radius: 0.5rem;
}

.dark {
  --background: 222.2 84% 4.9%;
  --foreground: 210 40% 98%;
  --card: 222.2 84% 4.9%;
  --card-foreground: 210 40% 98%;
  --popover: 222.2 84% 4.9%;
  --popover-foreground: 210 40% 98%;
  --primary: 217.2 91.2% 59.8%;
  --primary-foreground: 222.2 84% 4.9%;
  --secondary: 217.2 32.6% 17.5%;
  --secondary-foreground: 210 40% 98%;
  --muted: 217.2 32.6% 17.5%;
  --muted-foreground: 215 20.2% 65.1%;
  --accent: 217.2 32.6% 17.5%;
  --accent-foreground: 210 40% 98%;
  --destructive: 0 62.8% 30.6%;
  --destructive-foreground: 210 40% 98%;
  --border: 217.2 32.6% 17.5%;
  --input: 217.2 32.6% 17.5%;
  --ring: 224.3 76.3% 94.1%;
}

* {
  border-color: hsl(var(--border));
}

body {
  color: hsl(var(--foreground));
  background: hsl(var(--background));
}

.animate-fade-in {
  animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.bg-gradient-to-br {
  background-image: linear-gradient(to bottom right, var(--tw-gradient-stops));
}

.from-slate-50 {
  --tw-gradient-from: #f8fafc;
  --tw-gradient-to: rgb(248 250 252 / 0);
  --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to);
}

.to-slate-100 {
  --tw-gradient-to: #f1f5f9;
}

.dark .from-slate-900 {
  --tw-gradient-from: #0f172a;
  --tw-gradient-to: rgb(15 23 42 / 0);
  --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to);
}

.dark .to-slate-950 {
  --tw-gradient-to: #020617;
}

.text-brand-600 {
  color: #2563eb;
}

.dark .text-brand-400 {
  color: #60a5fa;
}

.bg-brand-500 {
  background-color: #3b82f6;
}

.bg-brand-600 {
  background-color: #2563eb;
}

.bg-gradient-to-br.from-brand-500.to-brand-600 {
  background-image: linear-gradient(to bottom right, #3b82f6, #2563eb);
}

/* Sidebar transition animations */
.flex-1.grid {
  transition: all 0.3s ease-in-out;
}

#sidebar nav a span:last-child {
  transition: opacity 0.3s ease-in-out;
}
</style>
