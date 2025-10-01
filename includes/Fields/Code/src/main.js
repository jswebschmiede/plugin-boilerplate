/**
 * External dependencies.
 */
import { Component } from '@wordpress/element';
import AceEditor from "react-ace";

// Import Ace Editor modes and themes
import "ace-builds/src-noconflict/mode-css";
import "ace-builds/src-noconflict/mode-javascript";
import "ace-builds/src-noconflict/mode-html";
import "ace-builds/src-noconflict/theme-github";
import "ace-builds/src-noconflict/theme-kuroir";
import "ace-builds/src-noconflict/theme-monokai";
import "ace-builds/src-noconflict/ext-language_tools";

class CodeField extends Component {
	/**
	 * Handles input changes.
	 *
	 * @param {string} value - The new editor value.
	 */
	handleChange = (value) => {
		const { field, onChange } = this.props;
		// Ensure value is a string and call onChange
		onChange(field.id, String(value || ''));
	};

	/**
	 * Renders the component.
	 *
	 * @return {Object}
	 */
	render() {
		const { field, name, value } = this.props;
		const mode = field.mode || 'css';
		const theme = field.theme || 'kuroir';
		const height = field.height || '300px';
		const width = field.width || '500px';

		return (
			<div className="cf-code__inner">
				<AceEditor
					mode={mode}
					theme={theme}
					id={'ace-editor-' + field.id}
					onChange={this.handleChange}
					value={value || ''}
					fontSize={14}
					lineHeight={19}
					showPrintMargin={true}
					showGutter={true}
					highlightActiveLine={true}
					width={width}
					height={height}
					editorProps={{
						$blockScrolling: true
					}}
					setOptions={{
						showLineNumbers: true,
						tabSize: 2,
						enableBasicAutocompletion: true,
						enableLiveAutocompletion: true,
						wrap: true,
						showPrintMargin: false,
						useWorker: false, // Disable web workers to avoid loading issues
					}}
				/>
				{/* Hidden input for form submission */}
				<input
					type="hidden"
					name={name}
					value={value || ''}
					id={field.id}
				/>
			</div>
		);
	}
}

export default CodeField;
