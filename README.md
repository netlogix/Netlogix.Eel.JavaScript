# Netlogix.Eel.JavaScript
Neos Eel Helper to embed inline JavaScript from files.

## Installation
```bash
composer require netlogix/eel-javascript
```

## Usage
You can use the JavaScriptHelper in your Fusion files like this:

### Scripts without Variables
To include scripts that don't require external variables, you can use the `embed` method.

```
script = ${Netlogix.JavaScript.embed('resource://Netlogix.Eel.JavaScript/Private/ScriptWithVars.js')}
```

### Scripts with Variables
To include scripts that require external variables, you can use the `embedWithVariables` method.

```
scriptWithVars = ${Netlogix.JavaScript.embedWithVariables('resource://Netlogix.Eel.JavaScript/Private/ScriptWithVars.js', {'someVariableName': 'someValue'})}
```

This will prefix the included javascript file with the variables passed to the method:

```javascript
var someVariable= 'someValue';
/// ... your script ScriptWithVars.js
```
