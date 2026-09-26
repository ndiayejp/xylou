import { describe, expect, it } from 'vitest';
import { elides } from '../elision';

describe('elides', () => {
    it.each(['Emma', 'Inès', 'Hugo', 'Yanis', 'Éloïse', 'ugo', ' Océane'])('« d’%s »', (name) => {
        expect(elides(name)).toBe(true);
    });

    it.each(['Lucas', 'Sophie', 'Noah', 'Jade'])('« de %s »', (name) => {
        expect(elides(name)).toBe(false);
    });
});
