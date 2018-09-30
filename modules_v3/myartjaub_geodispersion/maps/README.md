# Geographical Dispersion Maps

This folder contains the maps listed in the Administration page of the **Geographical Dispersion** module.

## Templates
Some templates are already provided in the */maps-fmt-name/* and */maps-fmt-name_code/* folders:
 - Use files in the */maps-fmt-name/* folder if the location is stored as the location name only (for instance `, Versailles,`),
 - Use files in the */maps-fmt-name_code/* folder if the location is stored as both location name and location code (for instance `, Versailles 78646,`),

## Mapping renamed locations 
Some locations may have changed names over the years, or merged into other locations, but if they can be related to a location listed in the map, it is possible to create a mapping by adding a section in the XML map description file, after the `subdivisions` tag:

```
    </subdivisions>
    <mappings>
        <mapping name="Constantinople" mapto="Istanbul" />
        ...
    </mappings>
</map>
```
where the value of the `name` attribute is the old name of the location (or the name of the location disappearing in case of a merge), and the one of the `mapto` attribute the new name.