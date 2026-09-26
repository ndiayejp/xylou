// Français : « de » devient « d’ » devant une voyelle ou un h (« la semaine d’Emma », « d’Hugo »).
// Le h aspiré (Hector…) n'est pas distingué : l'élision reste la forme la plus courante.
export function elides(word: string): boolean {
    return /^[aeiouyhàâäéèêëîïôöùûü]/i.test(word.trim());
}
