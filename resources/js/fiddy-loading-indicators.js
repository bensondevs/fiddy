document.addEventListener('livewire:init', () => {
    Livewire.hook('commit', ({ succeed }) => {
        succeed(() => {
            requestAnimationFrame(() => {
                document.querySelectorAll('.fi-loading-indicator').forEach((indicator) => {
                    indicator.style.removeProperty('display')
                })
            })
        })
    })
})
