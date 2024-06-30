/**
 * @license Copyright (c) 2014-2024, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */

import { ClassicEditor } from '@ckeditor/ckeditor5-editor-classic';
import { Alignment } from '@ckeditor/ckeditor5-alignment';
import { Autoformat } from '@ckeditor/ckeditor5-autoformat';
import { Bold, Code, Italic, Strikethrough, Underline } from '@ckeditor/ckeditor5-basic-styles';
import { BlockQuote } from '@ckeditor/ckeditor5-block-quote';
import { CodeBlock } from '@ckeditor/ckeditor5-code-block';
import type { EditorConfig } from '@ckeditor/ckeditor5-core';
import { Essentials } from '@ckeditor/ckeditor5-essentials';
import { FindAndReplace } from '@ckeditor/ckeditor5-find-and-replace';
import { FontBackgroundColor, FontColor, FontFamily, FontSize } from '@ckeditor/ckeditor5-font';
import { Heading } from '@ckeditor/ckeditor5-heading';
import { Highlight } from '@ckeditor/ckeditor5-highlight';
import { HorizontalLine } from '@ckeditor/ckeditor5-horizontal-line';
import { HtmlEmbed } from '@ckeditor/ckeditor5-html-embed';
import {
	DataFilter,
	DataSchema,
	FullPage,
	GeneralHtmlSupport,
	HtmlComment
} from '@ckeditor/ckeditor5-html-support';
import {
	AutoImage,
	Image,
	ImageCaption,
	ImageInsert,
	ImageResize,
	ImageStyle,
	ImageToolbar,
	ImageUpload
} from '@ckeditor/ckeditor5-image';
import { Indent, IndentBlock } from '@ckeditor/ckeditor5-indent';
import { TextPartLanguage } from '@ckeditor/ckeditor5-language';
import { AutoLink, Link, LinkImage } from '@ckeditor/ckeditor5-link';
import { List, ListProperties, TodoList } from '@ckeditor/ckeditor5-list';
import { MediaEmbed, MediaEmbedToolbar } from '@ckeditor/ckeditor5-media-embed';
import { Mention } from '@ckeditor/ckeditor5-mention';
import { Paragraph } from '@ckeditor/ckeditor5-paragraph';
import { PasteFromOffice } from '@ckeditor/ckeditor5-paste-from-office';
import { RemoveFormat } from '@ckeditor/ckeditor5-remove-format';
import { ShowBlocks } from '@ckeditor/ckeditor5-show-blocks';
import { SourceEditing } from '@ckeditor/ckeditor5-source-editing';
import {
	SpecialCharacters,
	SpecialCharactersArrows,
	SpecialCharactersCurrency,
	SpecialCharactersEssentials,
	SpecialCharactersLatin,
	SpecialCharactersMathematical,
	SpecialCharactersText
} from '@ckeditor/ckeditor5-special-characters';
import { Style } from '@ckeditor/ckeditor5-style';
import {
	Table,
	TableCaption,
	TableCellProperties,
	TableColumnResize,
	TableProperties,
	TableToolbar
} from '@ckeditor/ckeditor5-table';
import { TextTransformation } from '@ckeditor/ckeditor5-typing';
import { Undo } from '@ckeditor/ckeditor5-undo';
import { SimpleUploadAdapter } from '@ckeditor/ckeditor5-upload';
import { EditorWatchdog } from '@ckeditor/ckeditor5-watchdog';
import { WordCount } from '@ckeditor/ckeditor5-word-count';
import '../build/ckeditor5_custom.css';

// You can read more about extending the build with additional plugins in the "Installing plugins" guide.
// See https://ckeditor.com/docs/ckeditor5/latest/installation/plugins/installing-plugins.html for details.

class Editor extends ClassicEditor {
	public static override builtinPlugins = [
		Alignment,
		AutoImage,
		AutoLink,
		Autoformat,
		BlockQuote,
		Bold,
		Code,
		CodeBlock,
		DataFilter,
		DataSchema,
		Essentials,
		FindAndReplace,
		FontBackgroundColor,
		FontColor,
		FontFamily,
		FontSize,
		FullPage,
		GeneralHtmlSupport,
		Heading,
		Highlight,
		HorizontalLine,
		HtmlComment,
		HtmlEmbed,
		Image,
		ImageCaption,
		ImageInsert,
		ImageResize,
		ImageStyle,
		ImageToolbar,
		ImageUpload,
		Indent,
		IndentBlock,
		Italic,
		Link,
		LinkImage,
		List,
		ListProperties,
		MediaEmbed,
		MediaEmbedToolbar,
		Mention,
		Paragraph,
		PasteFromOffice,
		RemoveFormat,
		ShowBlocks,
		SimpleUploadAdapter,
		SourceEditing,
		SpecialCharacters,
		SpecialCharactersArrows,
		SpecialCharactersCurrency,
		SpecialCharactersEssentials,
		SpecialCharactersLatin,
		SpecialCharactersMathematical,
		SpecialCharactersText,
		Strikethrough,
		Style,
		Table,
		TableCaption,
		TableCellProperties,
		TableColumnResize,
		TableProperties,
		TableToolbar,
		TextPartLanguage,
		TextTransformation,
		TodoList,
		Underline,
		Undo,
		WordCount
	];

	public static override defaultConfig: EditorConfig = {
		toolbar: {
			items: [
				'findAndReplace',
				'|',
				'undo',
				'redo',
				'|',
				'alignment',
				'indent',
				'outdent',
				'horizontalLine',
				'|',
				'fontSize',
				'fontFamily',
				'fontColor',
				'fontBackgroundColor',
				'highlight',
				'|',
				'bold',
				'italic',
				'underline',
				'strikethrough',
				'removeFormat',
				'|',
				'specialCharacters',
				'|',
				'bulletedList',
				'numberedList',
				'todoList',
				'|',
				'code',
				'codeBlock',
				'blockQuote',
				'insertTable',
				'|',
				'link',
				'imageInsert',
				'mediaEmbed',
				'htmlEmbed',
				'|',
				'heading',
				'|',
				'style',
				'|',
				'textPartLanguage',
				'|',
				'sourceEditing',
				'showBlocks'
			],
			shouldNotGroupWhenFull: true
		},
		language: 'fr',
		image: {
			toolbar: [
				'imageTextAlternative',
				'toggleImageCaption',
				'imageStyle:inline',
				'imageStyle:block',
				'imageStyle:side',
				'linkImage'
			]
		},
		simpleUpload: {
            // The URL that the images are uploaded to.
            uploadUrl: '/upload',
            // Enable the XMLHttpRequest.withCredentials property.
            withCredentials: true,
            // Headers sent along with the XMLHttpRequest to the upload server.
            headers: {
                'X-CSRF-TOKEN': 'CSRF-Token',
                Authorization: 'Bearer <JSON Web Token>'
            }
        },
		table: {
			contentToolbar: [
				'tableColumn',
				'tableRow',
				'mergeTableCells',
				'tableCellProperties',
				'tableProperties'
			]
		}
	};
}

export default { Editor, EditorWatchdog };