export function controlClasses(invalid: boolean): string[] {
    return [
        'w-full rounded-input border-[1.5px] bg-surface text-body text-text placeholder:text-muted',
        'transition-colors duration-hover focus:ring-0',
        'disabled:cursor-not-allowed disabled:bg-disabled disabled:text-disabled-text',
        invalid
            ? 'border-danger focus:border-danger'
            : 'border-line hover:border-subtle focus:border-primary',
    ];
}
