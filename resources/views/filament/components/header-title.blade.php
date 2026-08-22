<div x-data="{ title: '' }" 
     x-init="
        title = document.title.split(' - ')[0].trim();
        document.addEventListener('livewire:navigated', () => { title = document.title.split(' - ')[0].trim(); });
     " 
     class="flex items-center gap-2 lg:ml-2">
    <span class="text-xl font-bold tracking-tight text-gray-900 dark:text-white" x-text="title"></span>
</div>
