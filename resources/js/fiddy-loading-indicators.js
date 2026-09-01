const resetLoadingIndicators = () => {
    requestAnimationFrame(() => {
        document.querySelectorAll('.fi-loading-indicator').forEach((indicator) => {
            indicator.style.removeProperty('display')
        })
    })
}

const registerLoadingIndicatorReset = () => {
    Livewire.hook('commit', ({ succeed }) => {
        succeed(() => {
            resetLoadingIndicators()
        })
    })
}

if (window.Livewire) {
    registerLoadingIndicatorReset()
} else {
    document.addEventListener('livewire:init', registerLoadingIndicatorReset)
}
