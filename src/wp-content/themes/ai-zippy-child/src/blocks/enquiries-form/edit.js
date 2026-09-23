import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextareaControl, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes, name }) {
	const subjectOptions = attributes.subjectOptions ?? [];
	const filmOptions = attributes.filmOptions ?? [];

	const updateSubjectOption = (index, value) => {
		setAttributes({
			subjectOptions: subjectOptions.map((option, i) => (i === index ? value : option)),
		});
	};

	const updateFilmOption = (index, value) => {
		setAttributes({
			filmOptions: filmOptions.map((option, i) => (i === index ? value : option)),
		});
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title="Content">
					<TextControl
						label="Eyebrow"
						value={attributes.eyebrow}
						onChange={(v) => setAttributes({ eyebrow: v })}
					/>
					<TextControl
						label="Title"
						value={attributes.title}
						onChange={(v) => setAttributes({ title: v })}
					/>
					<TextareaControl
						label="Sub"
						value={attributes.sub}
						onChange={(v) => setAttributes({ sub: v })}
					/>
				</PanelBody>
				<PanelBody title="Subject select" initialOpen={false}>
					<TextControl
						label="Subject option 1"
						value={subjectOptions[0]}
						onChange={(v) => updateSubjectOption(0, v)}
					/>
					<TextControl
						label="Subject option 2"
						value={subjectOptions[1]}
						onChange={(v) => updateSubjectOption(1, v)}
					/>
					<TextControl
						label="Subject option 3"
						value={subjectOptions[2]}
						onChange={(v) => updateSubjectOption(2, v)}
					/>
					<TextControl
						label="Feature film group label"
						value={attributes.filmGroupLabel}
						onChange={(v) => setAttributes({ filmGroupLabel: v })}
					/>
					<TextControl
						label="Film option 1"
						value={filmOptions[0]}
						onChange={(v) => updateFilmOption(0, v)}
					/>
					<TextControl
						label="Film option 2"
						value={filmOptions[1]}
						onChange={(v) => updateFilmOption(1, v)}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...useBlockProps()}>
				<ServerSideRender block={name} attributes={attributes} />
			</div>
		</>
	);
}
