# SerializationGroupBundle

SerializationGroupBundle provides a way to **organize serialization groups** through configuration.

It is useful to use with Symfony Serializer component or JMS Serializer.

## 1. Installation

Install the bundle through composer, then add it to your ``AppKernel.php`` file :

```php
$bundles = array(
    // ...
    new Gl3n\Bundle\SerializationGroupBundle\Gl3nSerializationGroupBundle(),
);
```

## 2. Configuration

Here is a sample configuration :

```yml
gl3n_serialization_group:
    groups:
        group2:
            roles: [ROLE_USER]
        group3:
            roles: [ROLE_ADMIN]
            include: [group2]
        group4:
            roles: [ROLE_SUPER_ADMIN]
            include: [group1, group3]
```

You can fill on each group :

- ``roles`` *(optional)* : an array of security roles allowed to use this group
- ``include`` *(optional)* : an array of included groups

## 3. Usage

Call the **serialization group resolver** (``gl3n_serialization_group.resolver``) in order to get the built groups list.

For example, with the previous sample configuration :

```php
// Resolving group1 returns ['group1']
$groups = $resolver->resolve('group1');

// Resolving group3 returns ['group2', 'group3']
$groups = $resolver->resolve('group3');

// Resolving group4 returns ['group1', 'group2', 'group3', 'group4']
$groups = $resolver->resolve('group4');
```

### 3.1. Authorization checker

Security roles are checked during resolution. If user has not the required role a ``Symfony\Component\Security\Core\Exception\AccessDeniedException`` is thrown.

### 3.2. Example

You can organise entity serialization groups by **size** (small, medium, large) and serialize several entities at once like that :

```yml
# config.yml
gl3n_serialization_group:
    groups:
        book_M:
            roles: [ROLE_USER]
            include: [book_S, author_S]
        book_L:
            roles: [ROLE_ADMIN]
            include: [book_M, author_M]
        author_M:
            include: [author_S]
```

In this example, a **book** has one or many **authors**.
