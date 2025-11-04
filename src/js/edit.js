/**
 * edit.js – Editor-Vorschau des Rating-Blocks
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';

export default function Edit() {
	const blockProps = useBlockProps({
		className: 'ud-rating-block',
	});

	return (
		<div {...blockProps}>
			<div className="ud-rating-block__preview">
				<p className="ud-rating-block__text">
					{__('(Fragetext wird im Frontend aus den Plugin-Einstellungen geladen)', 'rating-block-ud')}
				</p>
				<div className="ud-rating-block__stars">
					{[1, 2, 3, 4, 5].map((star) => (
						<span key={star} className="star">★</span>
					))}
				</div>
			</div>
		</div>
	);
}
