function normalizeForSearch(value) {
    return String(value ?? '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[!¡?,]/g, ' ')
        .replace(/\s+/g, ' ')
        .trim()
        .toLowerCase();
}

function textIncludesSearch(haystack, needle) {
    const normalizedNeedle = normalizeForSearch(needle);
    if (!normalizedNeedle) return true;
    return normalizeForSearch(haystack).includes(normalizedNeedle);
}
