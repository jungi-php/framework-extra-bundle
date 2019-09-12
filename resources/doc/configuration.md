Configuration
=============

```yaml
jungi_framework_extra:
    serializer: true # default
    entity_response:
        default_content_type: application/json # default
```

If you use the `symfony/serializer` component and you don't wish to use the available serializer adapters, set the `serializer` to `false`.  
