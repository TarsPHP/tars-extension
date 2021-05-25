#ifndef EXT_PHP8_WRAPPER_H_
#define EXT_PHP8_WRAPPER_H_

#define Z_LVAL_PP(v)          Z_LVAL_P(*v)
#define Z_STRVAL_PP(s)        Z_STRVAL_P(*s)
#define Z_ARRVAL_PP(s)        Z_ARRVAL_P(*s)
#define Z_STRLEN_PP(s)        Z_STRLEN_P(*s)
#define ALLOC_INIT_ZVAL(z)    zval z##Tmp; z = &(z##Tmp);

#define MY_Z_TYPE_P                     Z_TYPE_P
#define MY_Z_TYPE_PP(s)                 MY_Z_TYPE_P(*s)
#define MY_Z_ARRVAL_P(z)                Z_ARRVAL_P(z)->ht
#define MY_ZVAL_STRINGL(z, s, l, dup)   ZVAL_STRINGL(z, s, l)
#define MY_MAKE_STD_ZVAL(p)             zval _stack_zval_##p; p = &(_stack_zval_##p)
#define MY_RETVAL_STRINGL(s, l, dup)    RETVAL_STRINGL(s, l); if (dup == 0) efree(s)
#define MY_RETURN_STRINGL(s, l, dup)    RETURN_STRINGL(s, l)

#define MY_ZEND_REGISTER_RESOURCE(return_value, result, le_result)   \
        ZVAL_RES(return_value,zend_register_resource(result, le_result))

#define MY_ZEND_FETCH_RESOURCE(rsrc, rsrc_type, passed_id, default_id, resource_type_name, resource_type)  \
        rsrc = (rsrc_type) zend_fetch_resource(Z_RES_P(*passed_id), resource_type_name, resource_type);

#define my_smart_str                                                       smart_string
#define my_zval_ptr_dtor(p)                                                zval_ptr_dtor(*p)
#define my_zend_hash_next_index_insert(ht, pData, a, b)                    zend_hash_next_index_insert(ht, *pData)
#define my_zend_register_internal_class_ex(entry,parent_ptr,str)           zend_register_internal_class_ex(entry,parent_ptr)
#define my_add_assoc_string(array, key, value, duplicate)                  add_assoc_string(array, key, value)
#define my_add_assoc_stringl(__arg, __key, __str, __length, __duplicate)   add_assoc_stringl_ex(__arg, __key, strlen(__key), __str, __length)

static inline int my_zend_hash_add(HashTable *ht, char *k, long len, void *pData, int datasize, void **pDest)
{
    zval **real_p = pData;
    return zend_hash_str_add(ht, k, len - 1, *real_p) ? SUCCESS : FAILURE;
}

static inline int my_zend_hash_index_update(HashTable *ht, long key, void *pData, int datasize, void **pDest)
{
    zval **real_p = pData;
    return zend_hash_index_update(ht, key, *real_p) ? SUCCESS : FAILURE;
}

static zval *my_zend_read_property(zend_class_entry *class_ptr, zval *obj, char *s, int len, int silent)
{
    zval rv;
    return zend_read_property(class_ptr, Z_OBJ_P(obj), s, len, silent, &rv);
}

#endif /* EXT_PHP8_WRAPPER_H_ */
