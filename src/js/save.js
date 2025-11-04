/**
 * save.js
 *
 * Speichert die Blockdaten für das Frontend.
 * Da dieser Block per PHP (render.php) gerendert wird,
 * gibt diese Datei nur einen leeren Platzhalter zurück.
 */

import { useBlockProps } from '@wordpress/block-editor';

export default function save() {
	return <div {...useBlockProps.save()} />;
}
