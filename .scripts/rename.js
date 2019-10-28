#! /usr/bin/env node

// node .scripts/rename.js -o="Old Theme Pro" -n="New Theme Pro"

// Dependencies.
var fs   = require( 'fs' );
var path = require( 'path' );

// Process user arguments.
var args = process.argv.slice( 2 );

//Define variables.
var oldSlug,
	old_Slug,
	oldName,
	newSlug,
	new_Slug,
	newName,
	themeDir,
	stylesheet;

// Excluded folders.
var folderExclude = [
	'node_modules',
	'vendor',
	'vendors',
	'.git',
	'.sass-cache',
	'.hg',
	'.svn',
	'.CVS',
	'cache',
	'.scripts',
]

// Excluded files.
var fileExclude = [
	'rename.js'
]

// File extensions to work with.
var extensions = [
	'.php',
	'.html',
	'.js',
	'.json',
	'.css',
	'.scss',
	'.sass',
	'.txt',
	'.sh',
	'.md',
	'.dist',
	'.pot'
]

// Process arguments.
for ( var i = 0; i < args.length; i++ ) {
	if ( args[ i ].indexOf( '-o' ) != -1 ) {
		oldName = args[ i ].replace( '-o=', '' );
	} else if ( args[ i ].indexOf( '-n' ) != -1 ) {
		newName = args[ i ].replace( '-n=', '' );
	} else if ( args[ i ].indexOf( '-extensions' ) != -1 ) {
		extensions = args[ i ].replace( '-extensions=', '' ).split( ',' );
	} else if ( args[ i ].indexOf( '-folderExclude' ) != -1 ) {
		folderExclude = args[ i ].replace( '-folderExclude=', '' ).split( ',' );
	} else if ( args[ i ].indexOf( '-fileExclude' ) != -1 ) {
		fileExclude = args[ i ].replace( '-fileExclude=', '' ).split( ',' );
	}
}

if ( 'string' == typeof process.env.npm_config_oldname ) {
	oldName = process.env.npm_config_oldname;
}
if ( 'string' == typeof process.env.npm_config_newname ) {
	newName = process.env.npm_config_newname;
}

// Return if no slug is provided.
if ( ! oldName ) {
	console.log( 'No old name provided! Use --oldname="Old Name" --newname="New Name"' );
	return;
}

// Return in no name is provided.
if ( ! newName ) {
	console.log( 'No name provided! Use --oldname="Old Name" --newname="New Name"' );
	return;
}

// Read the themeDirectory.
themeDir = fs.readdirSync( '.' );

// If we have stylesheet, read it.
if ( typeof themeDir == 'object' && themeDir.indexOf( 'style.css' ) != -1 ) {
	stylesheet = fs.readFileSync( 'style.css', 'UTF8' );
} else {
	console.log( 'No style.css in the folder!' );
	// return
}

// Check for a dot and and a slash at the beginning of the folder name and add it if necessary.
if ( folderExclude.length > 0 ) {
	for ( var i = 0; i < folderExclude.length; i++ ) {		
		if ( folderExclude[i].indexOf( './' ) != 0 )
			folderExclude[i] = './' + folderExclude[i];
	};
}

// Check for a dot and and a slash at the beginning of the file name and add it if necessary.
if ( fileExclude.length > 0 ) {
	for ( var i = 0; i < fileExclude.length; i++ ) {		
		if ( fileExclude[i].indexOf( './' ) != 0 )
			fileExclude[i] = './' + fileExclude[i];
	};
}

// Check for a dot at the beginning of the extension name and add it if necessary.
if ( extensions.length > 0 ) {
	for ( var i = 0; i < extensions.length; i++ ) {		
		if ( extensions[i].indexOf( '.' ) != 0 )
			extensions[i] = '.' + extensions[i];
	};
}

oldSlug = oldName.toLowerCase().replace(/ /g,'-');
newSlug = newName.toLowerCase().replace(/ /g,'-');
old_Slug = oldName.toLowerCase().replace(/ /g,'_');
new_Slug = newName.toLowerCase().replace(/ /g,'_');

// Replace tasks.
function replaceStrings( file ) {

	// Get the content of the file.
	var content = fs.readFileSync( file, 'UTF8' );

	var old = content;

	// Replace text domain.
	content = content.replace(
		new RegExp( '\'' + oldSlug + '\'', 'g' ),
		'\'' + newSlug + '\''
	);

	// Replace the prefixes.
	content = content.replace(
		new RegExp( old_Slug + '_', 'g' ),
		new_Slug + '_'
	);

	// Replace text domain.
	content = content.replace(
		new RegExp( 'Text\sDomain:\s' + oldSlug, 'g' ),
		'Text Domain: ' + newSlug
	);

	// Replace the handles.
	content = content.replace(
		new RegExp( '\s' + oldSlug + '-', 'g' ),
		' ' + newSlug + '-'
	);

	// Replace Constants.
	content = content.replace(
		new RegExp( old_Slug.toUpperCase(), 'g' ),
		new_Slug.toUpperCase()
	);

	// Replace Constants.
	content = content.replace(
		new RegExp( oldName.toUpperCase(), 'g' ),
		newName.toUpperCase()
	);

	// Replace the prefixes.
	content = content.replace(
		new RegExp( oldSlug.toCamel(), 'g' ),
		newSlug.toCamel()
	);

	// Replace the prefixes.
	content = content.replace(
		new RegExp( oldSlug.to_Camel(), 'g' ),
		newSlug.to_Camel()
	);

	// Replace the prefixes.
	content = content.replace(
		new RegExp( oldSlug.toPascal(), 'g' ),
		newSlug.toPascal()
	);

	// Replace the prefixes.
	content = content.replace(
		new RegExp( oldSlug.to_Pascal(), 'g' ),
		newSlug.to_Pascal()
	);

	// Replace the prefixes.
	content = content.replace(
		new RegExp( old_Slug, 'g' ),
		new_Slug
	);

	// Replace the prefixes.
	content = content.replace(
		new RegExp( old_Slug.replace('_pro','') + '_', 'g' ),
		new_Slug + '_'
	);

	// Replace the prefixes.
	content = content.replace(
		new RegExp( oldSlug, 'g' ),
		newSlug
	);

	// Replace the prefixes.
	content = content.replace(
		new RegExp( oldSlug.replace('-pro',''), 'g' ),
		newSlug
	);

	// Replace the prefixes.
	content = content.replace(
		new RegExp( oldName, 'g' ),
		newName
	);

	// Replace the prefixes.
	content = content.replace(
		new RegExp( 'my\.studiopress\.com', 'g' ),
		'webplantmedia.com'
	);

	// Replace the prefixes.
	content = content.replace(
		new RegExp( 'www\.studiopress\.com', 'g' ),
		'webplantmedia.com'
	);

	// Replace the prefixes.
	content = content.replace(
		new RegExp( 'StudioPress', 'g' ),
		'Web Plant Media'
	);

	// Replace the prefixes.
	content = content.replace(
		new RegExp( 'studiopress', 'g' ),
		'webplantmedia'
	);

	// Write the file.
	fs.writeFileSync( file, content, 'UTF8' );

}

String.prototype.toPascal = function(){
	var s = this.replace(/\w+/g, function(w){return w[0].toUpperCase() + w.slice(1).toLowerCase();});
	return s.replace(/\-/g,'');
};

String.prototype.to_Pascal = function(){
	var s = this.replace(/\w+/g, function(w){return w[0].toUpperCase() + w.slice(1).toLowerCase();});
	return s.replace(/\-/g,'_');
};

String.prototype.toCamel = function(){
	return this.replace(/(\-[a-z])/g, function($1){return $1.toUpperCase().replace('-','');});
};

String.prototype.to_Camel = function(){
	return this.replace(/(\-[a-z])/g, function($1){return $1.toUpperCase().replace('-','_');});
};

/**
 * Walk the folder recursively and call the string replacing function.
 * @param  {string} dir Directory to walk
 */
function walkDir( dir ) {

	// Check if the directory name has a trailing slash and add it if necessary.
	if ( dir[ dir.length-1 ] != '/' ) {
		dir = dir.concat('/')
	}

	// Read all files and folders in the directory.
	items = fs.readdirSync( dir );
	
	// Loop through each of them.
	items.forEach( function( item ) {
		if ( fs.statSync( dir + item ).isDirectory() ) {
			if ( folderExclude.indexOf( dir + item ) == -1 ) {
				walkDir( dir + item + '/' );
			} 
		} else if ( fs.statSync( dir + item ).isFile() ) {
			if ( ( extensions.indexOf( path.extname( dir + item ) ) != -1 ) && ( fileExclude.indexOf( dir + item ) == -1 ) ) {
				replaceStrings( dir + item );
			}

			if ( item.includes(oldSlug) ) {
				// Replace the prefixes.
				newItem = item.replace(
					new RegExp( oldSlug, 'g' ),
					newSlug
				);

				// console.log(dir + item);
				// console.log(dir + newItem);

				fs.rename( dir + item, dir + newItem, function(err) {
					if ( err ) console.log('ERROR: ' + err);
				});
			}
		}
	} );
};

// Unleash the beast.
walkDir( '.' );

// Now start fresh with a new theme name!
console.log( 'All done!' );
