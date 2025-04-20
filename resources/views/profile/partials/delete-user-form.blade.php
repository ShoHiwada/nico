<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">アカウント削除</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            アカウントを完全に削除したい場合は、以下の操作を行ってください。この操作は取り消せません。
        </p>
    </header>

    <form method="post" action="{{ route('profile.destroy') }}" class="mt-6 space-y-6">
        @csrf
        @method('delete')

        <x-danger-button x-data="{ confirmingDeletion: false }"
                         x-on:click.prevent="confirmingDeletion = true"
                         x-show="!confirmingDeletion">
            アカウントを削除する
        </x-danger-button>

        <div x-show="confirmingDeletion" class="space-y-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                一度アカウントを削除すると、すべてのデータが完全に削除され、復元できません。
            </p>

            <x-input-label for="password" value="パスワードを入力" />
            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />

            <div class="flex gap-4">
                <x-secondary-button x-on:click="confirmingDeletion = false">キャンセル</x-secondary-button>
                <x-danger-button>削除する</x-danger-button>
            </div>
        </div>
    </form>
</section>
