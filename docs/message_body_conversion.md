# Message Body Conversion

The conversion is managed by `MessageBodyConversionManager`. Internally it delegates the conversion to registered mappers. By default, the `SerializerMapperAdapter` is used if available.

## Add/replace own mapper

Simply register your mapper by using the `jungi.message_conversion_mapper` tag with the `media-type` attribute \(`media_type` for yaml\).

```markup
<service id="custom_xml_mapper" class="App\Http\MessageConversion\CustomXmlMapper">
    <tag name="jungi.message_conversion_mapper" media-type="application/x-xml" />
    <tag name="jungi.message_conversion_mapper" media-type="application/xml" />
    <tag name="jungi.message_conversion_mapper" media-type="text/xml" />
    <!-- args -->
</service>
```

