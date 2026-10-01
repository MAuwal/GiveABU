<div x-data="{
    active: 0,
    showModal: false,
    modalProject: null,
    modalImage: '',
    modalImages: [],
    startSlider() {
        if (this.interval) clearInterval(this.interval);
        this.interval = setInterval(() => {
            if (this.projects.length) {
                this.active = (this.active + 1) % this.projects.length;
            }
        }, 4000);
    },
    interval: null,
    projects: @js($projects->map(function($p) {
        return [
            'id' => $p->id,
            'project_title' => $p->project_title,
            'project_description' => $p->project_description,
            'icon_image_url' => $p->icon_image_url,
            'created_at' => $p->created_at->toIso8601String(),
            'photos' => $p->photos->map(function($photo) { return ['image_url' => $photo->image_url]; }),
        ];
    })),
    humanTime(date) {
        return window.dayjs(date).fromNow();
    }
}" x-init="startSlider()" @keydown.window.escape="showModal = false" class="relative">
    <div class="lg:text-center mb-8">
        <h2 class="text-base text-indigo-600 font-semibold tracking-wide uppercase">Projects</h2>
        <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
            Explore Our Endowment Projects
        </p>
        <p class="mt-4 max-w-2xl text-xl text-gray-500 lg:mx-auto">
            See all the projects you can support and make a difference today.
        </p>
    </div>
    <div class="mt-8 flex overflow-x-auto space-x-6 pb-4 scrollbar-hide" style="scroll-behavior: smooth;">
        <template x-for="(project, idx) in projects" :key="project.id">
            <div class="min-w-[320px] max-w-xs bg-white rounded-lg shadow-lg p-6 flex flex-col justify-between transition-transform duration-300" :class="{ 'ring-2 ring-indigo-500': idx === active }" x-show="Math.abs(idx - active) <= 2">
                <img :src="project.icon_image_url || '/icon/Header.png'" alt="Project Image" class="h-40 w-full object-cover rounded-md mb-4">
                <h3 class="text-lg font-bold text-gray-900 mb-2" x-text="project.project_title"></h3>
                <p class="text-gray-600 mb-2 line-clamp-3" x-text="project.project_description"></p>
                <div class="text-xs text-gray-400 mb-4" x-text="humanTime(project.created_at)"></div>
                <button @click="showModal = true; modalProject = project; modalImages = project.photos.map(p => p.image_url); modalImage = modalImages[0] || (project.icon_image_url || '/icon/Header.png')" class="mt-auto bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Contribute</button>
            </div>
        </template>
    </div>
    <!-- Modal -->
    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
        <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full p-6 relative" @click.away="showModal = false">
            <button @click="showModal = false" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
            <h3 class="text-2xl font-bold mb-2" x-text="modalProject?.project_title"></h3>
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1 flex flex-col items-center">
                    <img :src="modalImage" alt="Project Image" class="h-64 w-full object-contain rounded mb-4 border-2 border-indigo-200">
                    <div class="flex space-x-2 overflow-x-auto mt-2">
                        <template x-for="(img, i) in modalImages" :key="i">
                            <img :src="img" @click="modalImage = img" :class="{'ring-2 ring-indigo-500': modalImage === img}" class="h-16 w-16 object-cover rounded cursor-pointer border border-gray-200">
                        </template>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="mb-4 text-gray-700" x-text="modalProject?.project_description"></p>
                    <div class="text-xs text-gray-400 mb-2" x-text="humanTime(modalProject?.created_at)"></div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/relativeTime.js"></script>
    <script>dayjs.extend(window.dayjs_plugin_relativeTime);</script>
</div>
